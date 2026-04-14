<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products')]
final class SearchController extends AbstractController
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    /**
     * GET /api/products/search?category_id=2&q=samsung&brand[]=Samsung&brand[]=Apple
     *     &price_min=100&price_max=1000&color[]=Black&os=Android&ram[]=8&ram[]=12
     *     &screen_size=6.7&sort=price_asc&page=1&limit=20
     */
    #[Route('/search', name: 'app_product_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $filters = [
            'category_id' => $request->query->get('category_id'),
            'q' => $request->query->get('q'),
            'brand' => $request->query->all('brand'),
            'price_min' => $request->query->get('price_min'),
            'price_max' => $request->query->get('price_max'),
            'color' => $request->query->all('color'),
            'os' => $request->query->all('os'),
            'ram' => $request->query->all('ram'),
            'screen_size' => $request->query->all('screen_size'),
            'sort' => $request->query->get('sort', 'relevance'),
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));

        $result = $this->searchService->search($filters, $page, $limit);

        return $this->json($result);
    }
}
