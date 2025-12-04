<?php

namespace App\Controller;

use App\Service\AttributeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products/attribute')]
final class ProductAttributeController extends AbstractController
{
    public function __construct(private readonly AttributeService $attributeService)
    {
    }

    #[Route('/', name: 'app_product_attribute', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your attributes controller!',
            'path' => 'src/Controller/ProductAttributeController.php',
        ]);
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
        ]);
    }
}
