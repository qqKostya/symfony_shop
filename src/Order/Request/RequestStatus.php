<?php

namespace App\Order\Request;

use Symfony\Component\Validator\Constraints as Assert;
use App\Order\Entity\Enum\OrderStatus;

final readonly class RequestStatus
{
    #[Assert\NotBlank(message: "Идентификатор пользователя обязателен")]
    #[Assert\Positive(message: "Идентификатор пользователя должен быть положительным числом")]
    public int $orderId;

    #[Assert\NotBlank(message: "Статус заказа обязателен")]
    #[Assert\Choice(callback: [OrderStatus::class, 'cases'], message: "Недопустимый статус заказа")]
    public string $status;

    public function __construct(int $orderId, string $status)
    {
        $this->userId = $orderId;
        $this->status = $status;
    }
}