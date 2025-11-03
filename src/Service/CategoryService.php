<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class CategoryService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public function create(array $request): Category
    {
        $category = new Category();
        $category->setName($request['name']);
        $category->setDescription($request['description']);
        $category->setIsActive($request['isActive']);
        $category->setSortOrder((int)$request['sortOrder']);
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setSlug(strtolower(str_replace(' ', '-', $request['name'])));
        //$category->setParent((int)$request['parent']);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    public function update(array $data, int $id): Category
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);
        $category->setName($data['name']);
        $category->setDescription($data['description']);
        $category->setIsActive($data['isActive']);
        $category->setSortOrder((int)$data['sortOrder']);
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    public function getAll(): array
    {
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        if (empty($categories)) {
            throw new NotFoundHttpException('Categories not found');
        }

        $result = [];

        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
                'isActive' => $category->isActive(),
                'sortOrder' => $category->getSortOrder(),
                'createdAt' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $category->getUpdatedAt()->format('Y-m-d H:i:s'),
                'parent' => $category->getParent()?->getId(),
            ];
        }

        return $result;
    }
}
