<?php

namespace App\Order\Request;

use Symfony\Component\Validator\Constraints as Assert;
use App\Order\Entity\Enum\DeliveryType;
use App\Order\Entity\Enum\OrderStatus;

final readonly class RequestCreateOrder
{
    #[Assert\NotBlank(message: "Идентификатор пользователя обязателен")]
    #[Assert\Positive(message: "Идентификатор пользователя должен быть положительным числом")]
    public int $userId;

    #[Assert\NotBlank(message: "Статус заказа обязателен")]
    #[Assert\Choice(callback: [OrderStatus::class, 'cases'], message: "Недопустимый статус заказа")]
    public string $status;

    #[Assert\NotBlank(message: "Тип доставки обязателен")]
    #[Assert\Choice(callback: [DeliveryType::class, 'cases'], message: "Недопустимый тип доставки")]
    public string $deliveryType;

    #[Assert\NotBlank(message: "Адрес доставки обязателен")]
    #[Assert\Valid]
    public RequestDeliveryAddress $deliveryAddress;

    // #[Assert\NotBlank(message: "Товары в заказе обязательны")]
    // #[Assert\Valid]
    // public RequestCreateOrderItemsCollection $items;
    #[Assert\NotBlank(message: "Товары в заказе обязательны")]
    #[Assert\Valid]
    public array $items;


    public function __construct(
        int $userId,
        string $status,
        string $deliveryType,
        RequestDeliveryAddress $deliveryAddress,
        array $items
    ) {
        $this->userId = $userId;
        $this->status = $status;
        $this->deliveryType = $deliveryType;
        $this->deliveryAddress = $deliveryAddress;
        $this->items = $items;
    }
}
