<?php

declare(strict_types=1);

namespace App\Order\Response;

use App\Order\Entity\OrderItem;
use Symfony\Component\Serializer\Annotation\Groups;

final class OrderResponse
{
    #[Groups(['order_read'])]
    public int $order_id;

    #[Groups(['order_items_read'])]
    public array $items;

    public function __construct(int $orderId, array $orderItems)
    {
        $this->order_id = $orderId;
        $this->items = array_map(static fn(OrderItem $item) => [
            'product_id' => $item->getProduct()->getId(),
            'quantity' => $item->getQuantity(),
            'created_at' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $item->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], $orderItems);
    }
}
