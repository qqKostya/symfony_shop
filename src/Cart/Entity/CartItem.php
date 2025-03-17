<?php

namespace App\Cart\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "cart_items")]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Cart::class)]
    #[ORM\JoinColumn(name: "cart_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private Cart $cart;

    #[ORM\Column(type: "integer")]
    #[Assert\Positive]
    private int $productId;

    #[ORM\Column(type: "integer")]
    #[Assert\Positive]
    private int $quantity;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $createdAt;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP", "onUpdate" => "CURRENT_TIMESTAMP"])]
    private \DateTime $updatedAt;
}
