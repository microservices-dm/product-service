<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\CategoryAttribute;
use App\Entity\ProductAttribute;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class AttributeService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public function create(array $request): ProductAttribute
    {
        $category = $this->entityManager->getRepository(Category::class)->find($request['category']);

        if (!$category) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Category not found');
        }

        $categoryAttribute = new CategoryAttribute();
        $categoryAttribute->setCategory($category);
        $categoryAttribute->setIsRequired(false);
        $this->entityManager->persist($categoryAttribute);

        $attribute = new ProductAttribute();
        $attribute->setName($request['name']);
        $attribute->addCategoryAttribute($categoryAttribute);
        $attribute->setType($request['type']);
        $attribute->setUnit($request['unit']);
        $attribute->setIsRequired($request['isRequired']);
        $attribute->setIsFilterable($request['isFilterable']);
        $attribute->setSortOrder((int)$request['sortOrder']);
        $attribute->setCreatedAt(new \DateTimeImmutable());
        $attribute->setSlug(strtolower(str_replace(' ', '-', $request['name'])));
        $this->entityManager->persist($attribute);
        $this->entityManager->flush();

        return $attribute;
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
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Categories not found');
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

    public function delete(int $id): void
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (empty($category)) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Category not found');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
