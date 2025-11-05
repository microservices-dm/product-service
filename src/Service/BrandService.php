<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Category;
use App\Exception\ApiException;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class BrandService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public function create(array $request): Brand
    {
        $brand = new Brand();
        $brand->setName($request['name']);
        $brand->setSlug(strtolower(str_replace(' ', '-', $request['name'])));
        $this->entityManager->persist($brand);
        $this->entityManager->flush();

        return $brand;
    }

    public function update(array $data, int $id): Brand
    {
        $brand = $this->entityManager->getRepository(Brand::class)->find($id);
        $brand->setName($data['name']);

        $this->entityManager->persist($brand);
        $this->entityManager->flush();

        return $brand;
    }

    public function getAll(): array
    {
        $brands = $this->entityManager->getRepository(Brand::class)->findAll();

        if (empty($brands)) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Brands not found');
        }

        $result = [];

        foreach ($brands as $brand) {
            $result[] = [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
                'slug' => $brand->getSlug(),
            ];
        }

        return $result;
    }

    public function delete(int $id): void
    {
        $brand = $this->entityManager->getRepository(Brand::class)->find($id);

        if (empty($brand)) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Brand not found');
        }

        $this->entityManager->remove($brand);
        $this->entityManager->flush();
    }
}
