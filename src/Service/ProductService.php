<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\CategoryAttribute;
use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Entity\ProductAttributeValue;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class ProductService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public function create(array $data, int $sellerId): Product
    {
        $category = $this->entityManager->getRepository(Category::class)->find($data['categoryId']);
        if (!$category) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Category not found');
        }

        $brand = $this->entityManager->getRepository(Brand::class)->find($data['brandId']);
        if (!$brand) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Brand not found');
        }

        $now = new \DateTimeImmutable();

        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice((string)$data['price']);
        $product->setDescription($data['description'] ?? null);
        $product->setCategory($category);
        $product->setBrand($brand);
        $product->setSellerId($sellerId);
        $product->setIsPublished($data['isPublished'] ?? false);
        $product->setSlug(strtolower(str_replace(' ', '-', $data['name'])));
        $product->setScu($this->generateScu($category, $brand));
        $product->setCreatedAt($now);
        $product->setUpdatedAt($now);

        $this->entityManager->persist($product);

        if (!empty($data['attributes'])) {
            $this->setAttributes($product, $data['attributes'], $category);
        }

        $this->entityManager->flush();

        return $product;
    }

    private function setAttributes(Product $product, array $attributes, Category $category): void
    {
        foreach ($attributes as $attr) {
            $productAttribute = $this->entityManager->getRepository(ProductAttribute::class)
                ->find($attr['attributeId']);

            if (!$productAttribute) {
                throw new ApiException(Response::HTTP_BAD_REQUEST, "Attribute {$attr['attributeId']} not found");
            }

            $categoryAttribute = $this->entityManager->getRepository(CategoryAttribute::class)
                ->findOneBy(['category' => $category, 'productAttribute' => $productAttribute]);

            if (!$categoryAttribute) {
                throw new ApiException(
                    Response::HTTP_BAD_REQUEST,
                    "Attribute '{$productAttribute->getName()}' does not belong to this category"
                );
            }

            $value = new ProductAttributeValue();
            $value->setProduct($product);
            $value->setAttribute($productAttribute);

            match ($productAttribute->getType()) {
                'string' => $value->setValueString((string)$attr['value']),
                'integer' => $value->setValueInteger((int)$attr['value']),
                'decimal' => $value->setValueDecimal((string)$attr['value']),
                'boolean' => $value->setValueBoolean((bool)$attr['value']),
                'text' => $value->setValueText((string)$attr['value']),
                default => throw new ApiException(
                    Response::HTTP_BAD_REQUEST,
                    "Unknown attribute type: {$productAttribute->getType()}"
                ),
            };

            $this->entityManager->persist($value);
            $product->addProductAttributeValue($value);
        }
    }

    private function generateScu(Category $category, Brand $brand): string
    {
        return strtoupper(
            substr($category->getSlug(), 0, 3) . '-'
            . substr($brand->getSlug(), 0, 3) . '-'
            . bin2hex(random_bytes(4))
        );
    }
}
