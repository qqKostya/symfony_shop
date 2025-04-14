<?php

declare(strict_types=1);

namespace App\Cart\Response;

use App\Cart\Entity\CartItem;
use Symfony\Component\Serializer\Annotation\Groups;

final class CartResponse
{
    #[Groups(['cart_read'])]
    public int $cart_id;

    /** @var CartItemResponse[] */
    #[Groups(['cart_items_read'])]
    public array $items;

    public function __construct(int $cartId, array $cartItems)
    {
        $this->cart_id = $cartId;
        $this->items = array_map(static fn(CartItem $item) => new CartItemResponse($item), $cartItems);
    }
}
