<?php

declare(strict_types=1);

namespace App\Order\Request;

use App\Order\Entity\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class StatusRequest
{
    #[Assert\NotBlank(message: 'Идентификатор пользователя обязателен')]
    #[Assert\Positive(message: 'Идентификатор пользователя должен быть положительным числом')]
    public int $userId;

    #[Assert\NotBlank(message: 'Номер заказа обязателен')]
    #[Assert\Positive(message: 'Номер заказа должен быть положительным числом')]
    public int $orderId;

    #[Assert\NotBlank(message: 'Статус заказа обязателен')]
    #[Assert\Choice(callback: [OrderStatus::class, 'getValues'], message: 'Недопустимый статус заказа')]
    public string $status;

    public function __construct(int $userId, int $orderId, string $status)
    {
        $this->userId = $userId;
        $this->orderId = $orderId;
        $this->status = $status;
    }
}
