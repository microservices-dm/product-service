<?php

namespace App\Controller;

use App\Service\BrandService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products/brand')]
final class BrandController extends AbstractController
{
    public function __construct(private readonly BrandService $brandService)
    {}

    #[Route('/create', name: 'app_create_brand', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->brandService->create($data);

        return $this->json([
            'id' => $result->getId(),
            'name' => $result->getName(),
            'slug' => $result->getSlug(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/update/{id}', name: 'app_update_brand', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->brandService->update($data, $id);

        return $this->json([
            'id' => $result->getId(),
            'name' => $result->getName(),
            'slug' => $result->getSlug(),
        ], Response::HTTP_RESET_CONTENT);
    }

    #[Route('/', name: 'app_get_brands', methods: ['GET'])]
    public function getAll(Request $request): JsonResponse
    {
        $result = $this->brandService->getAll();

        return $this->json($result, Response::HTTP_OK);
    }

    #[Route('/delete/{id}', name: 'app_delete_brand', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $this->brandService->delete($id);

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
