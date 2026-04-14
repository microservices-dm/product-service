<?php

declare(strict_types=1);

namespace App\Service;

use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested as NestedAggregation;
use Elastica\Aggregation\Range as RangeAggregation;
use Elastica\Aggregation\Terms as TermsAggregation;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use Elastica\Query\Nested;
use Elastica\Query\Range;
use Elastica\Query\Term;
use FOS\ElasticaBundle\Elastica\Index;

final readonly class SearchService
{
    public function __construct(
        private Index $productsIndex,
    ) {}

    public function search(array $filters, int $page = 1, int $limit = 20): array
    {
        $boolQuery = new BoolQuery();

        // Only published products
        $boolQuery->addFilter(new Term(['is_published' => true]));

        // Category filter
        if (!empty($filters['category_id'])) {
            $boolQuery->addFilter(new Term(['category.id' => (int) $filters['category_id']]));
        }

        // Text search
        if (!empty($filters['q'])) {
            $boolQuery->addMust(new MatchQuery('name', $filters['q']));
        }

        // Brand filter
        if (!empty($filters['brand'])) {
            $brands = is_array($filters['brand']) ? $filters['brand'] : [$filters['brand']];
            $brandBool = new BoolQuery();
            foreach ($brands as $brand) {
                $brandBool->addShould(new Term(['brand.name' => $brand]));
            }
            $brandBool->setMinimumShouldMatch(1);
            $boolQuery->addFilter($brandBool);
        }

        // Price range filter
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $rangeParams = [];
            if (!empty($filters['price_min'])) {
                $rangeParams['gte'] = (float) $filters['price_min'];
            }
            if (!empty($filters['price_max'])) {
                $rangeParams['lte'] = (float) $filters['price_max'];
            }
            $boolQuery->addFilter(new Range('price', $rangeParams));
        }

        // Nested attribute filters
        $this->applyAttributeFilter($boolQuery, $filters, 'color', 'value_string');
        $this->applyAttributeFilter($boolQuery, $filters, 'os', 'value_string', 'operatingsystem');
        $this->applyAttributeFilter($boolQuery, $filters, 'ram', 'value_integer', 'ramsize');
        $this->applyAttributeFilter($boolQuery, $filters, 'screen_size', 'value_decimal', 'screensize');

        $query = new Query($boolQuery);
        $query->setFrom(($page - 1) * $limit);
        $query->setSize($limit);

        // Sorting
        $sort = $filters['sort'] ?? 'relevance';
        match ($sort) {
            'price_asc' => $query->setSort(['price' => 'asc']),
            'price_desc' => $query->setSort(['price' => 'desc']),
            'newest' => $query->setSort(['created_at' => 'desc']),
            default => null,
        };

        // Aggregations (facets)
        $this->addAggregations($query);

        $resultSet = $this->productsIndex->search($query);

        // Format products from ES results
        $products = [];

        foreach ($resultSet->getResults() as $result) {
            $source = $result->getSource();
            $source['id'] = (int) $result->getId();
            $products[] = $source;
        }

        // Format aggregations
        $facets = $this->formatAggregations($resultSet->getAggregations());

        return [
            'products' => $products,
            'facets' => $facets,
            'total' => $resultSet->getTotalHits(),
            'page' => $page,
            'limit' => $limit,
        ];
    }

    private function applyAttributeFilter(
        BoolQuery $boolQuery,
        array $filters,
        string $filterKey,
        string $valueField,
        ?string $attributeSlug = null,
    ): void {
        if (empty($filters[$filterKey])) {
            return;
        }

        $values = is_array($filters[$filterKey]) ? $filters[$filterKey] : [$filters[$filterKey]];
        $slug = $attributeSlug ?? $filterKey;

        foreach ($values as $value) {
            $nestedBool = new BoolQuery();
            $nestedBool->addMust(new Term(['attributes.slug' => $slug]));

            if ($valueField === 'value_integer') {
                $nestedBool->addMust(new Term(['attributes.value_integer' => (int) $value]));
            } elseif ($valueField === 'value_decimal') {
                $nestedBool->addMust(new Term(['attributes.value_decimal' => (float) $value]));
            } else {
                $nestedBool->addMust(new Term(['attributes.value_string' => $value]));
            }

            $nested = new Nested();
            $nested->setPath('attributes');
            $nested->setQuery($nestedBool);

            $boolQuery->addFilter($nested);
        }
    }

    private function addAggregations(Query $query): void
    {
        // Brand facet
        $brandAgg = new TermsAggregation('brands');
        $brandAgg->setField('brand.name');
        $brandAgg->setSize(50);
        $query->addAggregation($brandAgg);

        // Price ranges facet
        $priceAgg = new RangeAggregation('price_ranges');
        $priceAgg->setField('price');
        $priceAgg->addRange(0, 300);
        $priceAgg->addRange(300, 600);
        $priceAgg->addRange(600, 1000);
        $priceAgg->addRange(1000, 1500);
        $priceAgg->addRange(1500);
        $query->addAggregation($priceAgg);

        // Color facet (nested)
        $colorNestedAgg = new NestedAggregation('colors', 'attributes');
        $colorFilter = new Filter('color_filter', new Term(['attributes.slug' => 'color']));
        $colorTerms = new TermsAggregation('color_values');
        $colorTerms->setField('attributes.value_string');
        $colorTerms->setSize(50);
        $colorFilter->addAggregation($colorTerms);
        $colorNestedAgg->addAggregation($colorFilter);
        $query->addAggregation($colorNestedAgg);

        // OS facet (nested)
        $osNestedAgg = new NestedAggregation('operating_systems', 'attributes');
        $osFilter = new Filter('os_filter', new Term(['attributes.slug' => 'operatingsystem']));
        $osTerms = new TermsAggregation('os_values');
        $osTerms->setField('attributes.value_string');
        $osTerms->setSize(20);
        $osFilter->addAggregation($osTerms);
        $osNestedAgg->addAggregation($osFilter);
        $query->addAggregation($osNestedAgg);

        // RAM facet (nested)
        $ramNestedAgg = new NestedAggregation('ram_sizes', 'attributes');
        $ramFilter = new Filter('ram_filter', new Term(['attributes.slug' => 'ramsize']));
        $ramTerms = new TermsAggregation('ram_values');
        $ramTerms->setField('attributes.value_integer');
        $ramTerms->setSize(20);
        $ramFilter->addAggregation($ramTerms);
        $ramNestedAgg->addAggregation($ramFilter);
        $query->addAggregation($ramNestedAgg);

        // Screen size facet (nested)
        $screenNestedAgg = new NestedAggregation('screen_sizes', 'attributes');
        $screenFilter = new Filter('screen_filter', new Term(['attributes.slug' => 'screensize']));
        $screenTerms = new TermsAggregation('screen_values');
        $screenTerms->setField('attributes.value_decimal');
        $screenTerms->setSize(20);
        $screenFilter->addAggregation($screenTerms);
        $screenNestedAgg->addAggregation($screenFilter);
        $query->addAggregation($screenNestedAgg);
    }

    private function formatAggregations(array $aggregations): array
    {
        $facets = [];

        // Brands
        if (isset($aggregations['brands']['buckets'])) {
            $facets['brands'] = array_map(
                fn(array $bucket) => ['value' => $bucket['key'], 'count' => $bucket['doc_count']],
                $aggregations['brands']['buckets'],
            );
        }

        // Price ranges
        if (isset($aggregations['price_ranges']['buckets'])) {
            $facets['price_ranges'] = array_map(
                fn(array $bucket) => [
                    'from' => $bucket['from'] ?? null,
                    'to' => $bucket['to'] ?? null,
                    'count' => $bucket['doc_count'],
                ],
                $aggregations['price_ranges']['buckets'],
            );
        }

        // Colors
        if (isset($aggregations['colors']['color_filter']['color_values']['buckets'])) {
            $facets['colors'] = array_map(
                fn(array $bucket) => ['value' => $bucket['key'], 'count' => $bucket['doc_count']],
                $aggregations['colors']['color_filter']['color_values']['buckets'],
            );
        }

        // OS
        if (isset($aggregations['operating_systems']['os_filter']['os_values']['buckets'])) {
            $facets['operating_systems'] = array_map(
                fn(array $bucket) => ['value' => $bucket['key'], 'count' => $bucket['doc_count']],
                $aggregations['operating_systems']['os_filter']['os_values']['buckets'],
            );
        }

        // RAM
        if (isset($aggregations['ram_sizes']['ram_filter']['ram_values']['buckets'])) {
            $facets['ram_sizes'] = array_map(
                fn(array $bucket) => ['value' => $bucket['key'], 'count' => $bucket['doc_count']],
                $aggregations['ram_sizes']['ram_filter']['ram_values']['buckets'],
            );
        }

        // Screen sizes
        if (isset($aggregations['screen_sizes']['screen_filter']['screen_values']['buckets'])) {
            $facets['screen_sizes'] = array_map(
                fn(array $bucket) => ['value' => $bucket['key'], 'count' => $bucket['doc_count']],
                $aggregations['screen_sizes']['screen_filter']['screen_values']['buckets'],
            );
        }

        return $facets;
    }
}
