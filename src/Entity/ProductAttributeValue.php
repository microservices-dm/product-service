<?php

namespace App\Entity;

use App\Repository\ProductAttributeValueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductAttributeValueRepository::class)]
class ProductAttributeValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productAttributeValues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'productAttributeValues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductAttribute $attribute = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valueString = null;

    #[ORM\Column(nullable: true)]
    private ?int $valueInteger = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $valueDecimal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $valueBoolean = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $valueText = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getAttribute(): ?ProductAttribute
    {
        return $this->attribute;
    }

    public function setAttribute(?ProductAttribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getValueString(): ?string
    {
        return $this->valueString;
    }

    public function setValueString(?string $valueString): static
    {
        $this->valueString = $valueString;

        return $this;
    }

    public function getValueInteger(): ?int
    {
        return $this->valueInteger;
    }

    public function setValueInteger(?int $valueInteger): static
    {
        $this->valueInteger = $valueInteger;

        return $this;
    }

    public function getValueDecimal(): ?string
    {
        return $this->valueDecimal;
    }

    public function setValueDecimal(?string $valueDecimal): static
    {
        $this->valueDecimal = $valueDecimal;

        return $this;
    }

    public function isValueBoolean(): ?bool
    {
        return $this->valueBoolean;
    }

    public function setValueBoolean(?bool $valueBoolean): static
    {
        $this->valueBoolean = $valueBoolean;

        return $this;
    }

    public function getValueText(): ?string
    {
        return $this->valueText;
    }

    public function setValueText(?string $valueText): static
    {
        $this->valueText = $valueText;

        return $this;
    }
}
