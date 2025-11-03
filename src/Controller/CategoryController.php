<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products/category')]
final class CategoryController extends AbstractController
{
    public function __construct(private CategoryService $categoryService)
    {}

    #[Route('/create', name: 'app_create_category', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->categoryService->create($data);

        return $this->json([
            'id' => $result->getId(),
            'name' => $result->getName(),
            'description' => $result->getDescription(),
            'parent' => $result->getParent()?->getId(),
            'isActive' => $result->isActive(),
            'sortOrder' => $result->getSortOrder(),
            'createdAt' => $result->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $result->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], Response::HTTP_CREATED);
    }

    #[Route('/update/{id}', name: 'app_update_category', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->categoryService->update($data, $id);

        return $this->json([
            'id' => $result->getId(),
            'name' => $result->getName(),
            'description' => $result->getDescription(),
            'parent' => $result->getParent()?->getId(),
            'isActive' => $result->isActive(),
            'sortOrder' => $result->getSortOrder(),
            'createdAt' => $result->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $result->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], Response::HTTP_RESET_CONTENT);
    }

    #[Route('/', name: 'app_get_categories', methods: ['GET'])]
    public function getAll(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $result = $this->categoryService->getAll();

        return $this->json($result, Response::HTTP_OK);
    }
}
