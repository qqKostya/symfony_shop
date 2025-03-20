<?php

namespace App\Product\Entity;

use App\Product\Serializer\SerializationGroups;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Cart\Serializer\SerializationGroups as SerializationGroupsCart;

#[ORM\Entity]
#[ORM\Table(name: "products")]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroupsCart::CART_ITEMS_READ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroups::PRODUCT_WRITE])]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroups::PRODUCT_WRITE])]
    private ?string $description;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\PositiveOrZero]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroups::PRODUCT_WRITE])]
    private int $cost;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\PositiveOrZero]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroups::PRODUCT_WRITE])]
    private int $tax;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroups::PRODUCT_WRITE])]
    private ?int $weight;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroups::PRODUCT_WRITE])]
    private ?int $height;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroups::PRODUCT_WRITE])]
    private ?int $width;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([SerializationGroups::PRODUCT_READ, SerializationGroups::PRODUCT_WRITE])]
    private ?int $length;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    #[SerializedName('createdAt')]
    #[Groups([SerializationGroups::PRODUCT_READ])]
    private \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "update")]
    #[SerializedName('updatedAt')]
    #[Groups([SerializationGroups::PRODUCT_READ])]
    private \DateTime $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }

    public function getTax(): int
    {
        return $this->tax;
    }

    public function setTax(int $tax): void
    {
        $this->tax = $tax;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): void
    {
        $this->weight = $weight;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): void
    {
        $this->length = $length;
    }
}
