<?php

declare(strict_types=1);

namespace App\Order\Entity;

use App\Order\Entity\Enum\DeliveryType;
use App\Order\Entity\Enum\OrderStatus;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity]
#[ORM\Table(name: 'orders', schema: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::PAID;

    #[ORM\Column(enumType: DeliveryType::class)]
    private DeliveryType $deliveryType = DeliveryType::COURIER;

    #[ORM\Column(type: Types::JSON)]
    private array $deliveryAddress;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[SerializedName('createdAt')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    #[SerializedName('updatedAt')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        User $user,
        OrderStatus $status,
        array $deliveryAddress,
        DeliveryType $deliveryType,
    ) {
        $this->user = $user;
        $this->status = $status;
        $this->deliveryAddress = $deliveryAddress;
        $this->deliveryType = $deliveryType;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getDeliveryAddress(): array
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(array $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    public function setStatus(OrderStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setDeliveryType(DeliveryType $deliveryType): self
    {
        $this->deliveryType = $deliveryType;

        return $this;
    }

    public function getDeliveryType(): DeliveryType
    {
        return $this->deliveryType;
    }
}
