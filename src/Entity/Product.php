<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "products")]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description;

    #[ORM\Column(type: "integer")]
    #[Assert\PositiveOrZero]
    private int $cost;

    #[ORM\Column(type: "integer")]
    #[Assert\PositiveOrZero]
    private int $tax;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $weight;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $height;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $width;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $length;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $createdAt;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP", "onUpdate" => "CURRENT_TIMESTAMP"])]
    private \DateTime $updatedAt;
}
