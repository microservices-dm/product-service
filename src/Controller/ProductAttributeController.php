<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products/attribute')]
final class ProductAttributeController extends AbstractController
{
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
        dd($request->getContent());

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProductController.php',
        ]);
    }
}
