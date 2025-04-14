<?php

declare(strict_types=1);

namespace App\Cart\Response;

use App\Cart\Entity\CartItem;

final class CartItemResponse
{
    #[Groups(['cart_items_read'])]
    public int $product_id;

    #[Groups(['cart_items_read'])]
    public int $quantity;

    public function __construct(CartItem $item)
    {
        $this->product_id = $item->getProduct()->getId();
        $this->quantity = $item->getQuantity();
    }
}
