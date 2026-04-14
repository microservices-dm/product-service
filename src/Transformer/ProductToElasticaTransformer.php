<?php

declare(strict_types=1);

namespace App\Transformer;

use App\Entity\Product;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

final readonly class ProductToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * @param Product $object
     */
    public function transform(object $object, array $fields): Document
    {
        $attributes = [];
        foreach ($object->getProductAttributeValues() as $pav) {
            $attr = $pav->getAttribute();
            $attributes[] = [
                'id' => $attr->getId(),
                'name' => $attr->getName(),
                'slug' => $attr->getSlug(),
                'type' => $attr->getType(),
                'value_string' => $pav->getValueString(),
                'value_integer' => $pav->getValueInteger(),
                'value_decimal' => $pav->getValueDecimal() !== null ? (float) $pav->getValueDecimal() : null,
                'value_boolean' => $pav->isValueBoolean(),
            ];
        }

        $data = [
            'name' => $object->getName(),
            'slug' => $object->getSlug(),
            'description' => $object->getDescription(),
            'price' => (float) $object->getPrice(),
            'is_published' => $object->isPublished(),
            'seller_id' => $object->getSellerId(),
            'created_at' => $object->getCreatedAt()->format('Y-m-d H:i:s'),
            'brand' => [
                'id' => $object->getBrand()->getId(),
                'name' => $object->getBrand()->getName(),
                'slug' => $object->getBrand()->getSlug(),
            ],
            'category' => [
                'id' => $object->getCategory()->getId(),
                'name' => $object->getCategory()->getName(),
                'slug' => $object->getCategory()->getSlug(),
            ],
            'attributes' => $attributes,
        ];

        return new Document((string) $object->getId(), $data);
    }
}