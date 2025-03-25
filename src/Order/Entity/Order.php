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
use Symfony\Component\Validator\Constraints as Assert;

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

    #[ORM\Column(type: Types::STRING)]
    #[Assert\Choice(callback: [OrderStatus::class, 'cases'])]
    private string $status;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\Choice(callback: [DeliveryType::class, 'cases'])]
    private string $deliveryType;

    #[ORM\Column(type: Types::JSON)]
    private array $deliveryAddress;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[SerializedName('createdAt')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    #[SerializedName('updatedAt')]
    private \DateTimeImmutable $updatedAt;

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
