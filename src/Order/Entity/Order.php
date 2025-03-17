<?php

namespace App\Order\Entity;

use App\Order\Entity\Enum\DeliveryType;
use App\Order\Entity\Enum\OrderStatus;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "orders")]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: "string")]
    #[Assert\Choice(callback: [OrderStatus::class, 'cases'])]
    private string $status;

    #[ORM\Column(type: "string")]
    #[Assert\Choice(callback: [DeliveryType::class, 'cases'])]
    private string $deliveryType;

    #[ORM\Column(type: "json")]
    private array $deliveryAddress;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $createdAt;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP", "onUpdate" => "CURRENT_TIMESTAMP"])]
    private \DateTime $updatedAt;

    public function setStatus(OrderStatus $status): self
    {
        $this->status = $status->value;
        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return OrderStatus::from($this->status);
    }

    public function setDeliveryType(DeliveryType $deliveryType): self
    {
        $this->deliveryType = $deliveryType->value;
        return $this;
    }

    public function getDeliveryType(): DeliveryType
    {
        return DeliveryType::from($this->deliveryType);
    }
}
