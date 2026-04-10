<?php

namespace App\Controller;

use App\Service\AttributeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products/attribute')]
final class ProductAttributeController extends AbstractController
{
    public function __construct(private readonly AttributeService $attributeService)
    {
    }

    #[Route('/category/{categoryId}', name: 'app_product_attribute_by_category', methods: ['GET'])]
    public function getByCategory(int $categoryId): JsonResponse
    {
        return $this->json($this->attributeService->getByCategoryId($categoryId));
    }

    #[Route('/category/{categoryId}/values/{attributeId}', name: 'app_product_attribute_values', methods: ['GET'])]
    public function getValues(int $categoryId, int $attributeId): JsonResponse
    {
        return $this->json($this->attributeService->getValuesByAttribute($categoryId, $attributeId));
    }

    #[Route('/create', name: 'app_create_product_attribute', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->attributeService->create($data);

        return $this->json([
            'id' => $result->getId(),
            'name' => $result->getName(),
            'type' => $result->getType(),
            'unit' => $result->getUnit(),
            'isRequired' => $result->isRequired(),
            'isFilterable' => $result->isFilterable(),
            'createdAt' => $result->getCreatedAt()->format('Y-m-d H:i:s'),
            'category' => $result->getCategoryAttributes()->first()->getCategory()->getName(),
            'sortOrder' => $result->getSortOrder(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/update/{id}', name: 'app_update_product_attribute', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->attributeService->update($data, $id);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/delete/{id}', name: 'app_delete_product_attribute', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->attributeService->delete($id);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
