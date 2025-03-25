<?php

declare(strict_types=1);

namespace App\Cart\Response;

use App\Cart\Entity\CartItem;
use Symfony\Component\Serializer\Annotation\Groups;

final class CartResponse
{
    #[Groups(['cart_read'])]
    public int $cart_id;

    #[Groups(['cart_items_read'])]
    public array $items;

    public function __construct(int $cartId, array $cartItems)
    {
        $this->cart_id = $cartId;
        $this->items = array_map(static fn(CartItem $item) => [
            'product_id' => $item->getProduct()->getId(),
            'quantity' => $item->getQuantity(),
            'created_at' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $item->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], $cartItems);
    }
}
