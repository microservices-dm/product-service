<?php

namespace App\Controller;

use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products')]
final class ProductController extends AbstractController
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    #[Route('/create', name: 'app_create_product', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sellerId = (int)$request->headers->get('X-User-Id');

        $product = $this->productService->create($data, $sellerId);

        $attributes = [];
        foreach ($product->getProductAttributeValues() as $pav) {
            $attributes[] = [
                'attributeId' => $pav->getAttribute()->getId(),
                'name' => $pav->getAttribute()->getName(),
                'value' => $pav->getValueString()
                    ?? $pav->getValueInteger()
                    ?? $pav->getValueDecimal()
                    ?? $pav->isValueBoolean()
                    ?? $pav->getValueText(),
            ];
        }

        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'slug' => $product->getSlug(),
            'scu' => $product->getScu(),
            'description' => $product->getDescription(),
            'categoryId' => $product->getCategory()->getId(),
            'brandId' => $product->getBrand()->getId(),
            'sellerId' => $product->getSellerId(),
            'isPublished' => $product->isPublished(),
            'attributes' => $attributes,
            'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
        ], Response::HTTP_CREATED);
    }
}
