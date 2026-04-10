<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\CategoryAttribute;
use App\Entity\ProductAttribute;
use App\Enum\Color;
use App\Enum\OperatingSystem;
use App\Enum\RamSize;
use App\Enum\ScreenSize;
use App\Enum\StorageCapacity;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;
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

    public function update(array $data, int $id): ProductAttribute
    {
        $attribute = $this->entityManager->getRepository(ProductAttribute::class)->find($id);

        if (!$attribute) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Attribute not found');
        }

        $attribute->setName($data['name']);
        $attribute->setType($data['type']);
        $attribute->setUnit($data['unit'] ?? null);
        $attribute->setIsRequired($data['isRequired']);
        $attribute->setIsFilterable($data['isFilterable']);
        $attribute->setSortOrder((int)$data['sortOrder']);
        $attribute->setSlug(strtolower(str_replace(' ', '-', $data['name'])));

        $this->entityManager->flush();

        return $attribute;
    }

    public function getByCategoryId(int $categoryId): array
    {
        $category = $this->entityManager->getRepository(Category::class)->find($categoryId);

        if (!$category) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Category not found');
        }

        $categoryAttributes = $this->entityManager->getRepository(CategoryAttribute::class)
            ->findBy(['category' => $category]);

        if (empty($categoryAttributes)) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'No attributes found for this category');
        }

        $result = [];

        foreach ($categoryAttributes as $ca) {
            $attribute = $ca->getProductAttribute();
            $result[] = [
                'id' => $attribute->getId(),
                'name' => $attribute->getName(),
                'slug' => $attribute->getSlug(),
                'type' => $attribute->getType(),
                'unit' => $attribute->getUnit(),
                'isRequired' => $ca->isRequired(),
                'isFilterable' => $attribute->isFilterable(),
                'sortOrder' => $attribute->getSortOrder(),
            ];
        }

        return $result;
    }

    private const array ATTRIBUTE_ENUMS = [
        'color' => Color::class,
        'screen_size' => ScreenSize::class,
        'storage' => StorageCapacity::class,
        'ram' => RamSize::class,
        'os' => OperatingSystem::class,
    ];

    public function getValuesByAttribute(int $categoryId, int $attributeId): array
    {
        $categoryAttribute = $this->entityManager->getRepository(CategoryAttribute::class)
            ->findOneBy(['category' => $categoryId, 'productAttribute' => $attributeId]);

        if (!$categoryAttribute) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Attribute not found in this category');
        }

        $type = $categoryAttribute->getProductAttribute()->getName();
        $enumClass = self::ATTRIBUTE_ENUMS[$type] ?? null;

        if (!$enumClass) {
            throw new ApiException(Response::HTTP_NOT_FOUND, "No predefined values for attribute type: $type");
        }

        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            $enumClass::cases()
        );
    }

    public function delete(int $id): void
    {
        $attribute = $this->entityManager->getRepository(ProductAttribute::class)->find($id);

        if (!$attribute) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Attribute not found');
        }

        $this->entityManager->remove($attribute);
        $this->entityManager->flush();
    }
}
