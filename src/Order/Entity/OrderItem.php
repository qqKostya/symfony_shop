<?php

namespace App\Order\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "order_items")]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Order::class)]
    #[ORM\JoinColumn(name: "order_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private Order $order;

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
