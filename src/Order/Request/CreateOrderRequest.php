<?php

declare(strict_types=1);

namespace App\Order\Request;

use App\Order\Entity\Enum\DeliveryType;
use App\Order\Entity\Enum\OrderStatus;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrderRequest
{
    #[Assert\NotBlank(message: 'Идентификатор пользователя обязателен')]
    #[Assert\Positive(message: 'Идентификатор пользователя должен быть положительным числом')]
    public int $userId;

    #[Assert\NotBlank(message: 'Статус заказа обязателен')]
    #[Assert\Choice(callback: [OrderStatus::class, 'getValues'], message: 'Недопустимый статус заказа')]
    public string $status;

    #[Assert\NotBlank(message: 'Тип доставки обязателен')]
    // #[Assert\Choice(callback: [DeliveryType::class, 'cases'], message: "Недопустимый тип доставки")]
    #[Assert\Choice(callback: [DeliveryType::class, 'getValues'], message: 'Недопустимый статус доставки')]
    public string $deliveryType;

    #[Assert\NotBlank(message: 'Адрес доставки обязателен')]
    #[Assert\Valid]
    public DeliveryAddressRequest $deliveryAddress;

    // #[Assert\NotBlank(message: "Товары в заказе обязательны")]
    // #[Assert\Valid]
    // public CreateOrderItemsCollectionRequest $items;
    #[Assert\NotBlank(message: 'Товары в заказе обязательны')]
    #[Assert\Count(min: 1, minMessage: 'В заказе должен быть хотя бы один товар')]
    #[Assert\Count(max: 20, maxMessage: 'В заказе не может быть более 20 позиций')]
    #[Assert\Valid]
    #[SerializedName('items')]  // Используем SerializedName для правильной обработки имени свойства
    public array $items;  // Это будет массив объектов CreateOrderItemsRequest

    public function __construct(
        int $userId,
        string $status,
        string $deliveryType,
        DeliveryAddressRequest $deliveryAddress,
        array $items,
    ) {
        $this->userId          = $userId;
        $this->status          = $status;
        $this->deliveryType    = $deliveryType;
        $this->deliveryAddress = $deliveryAddress;
        $this->items = [];
        foreach ($items as $itemData) {
            if (isset($itemData['productId'], $itemData['quantity'])) {
                $this->items[] = new CreateOrderItemsRequest(
                    $itemData['productId'],
                    $itemData['quantity'],
                );
            }
        }
    }
}
