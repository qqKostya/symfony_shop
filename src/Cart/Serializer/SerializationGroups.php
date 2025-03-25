<?php

declare(strict_types=1);

namespace App\Cart\Serializer;

final class SerializationGroups
{
    public const CART_READ       = 'cart:read';
    public const CART_WRITE      = 'cart:write';
    public const CART_ITEMS_READ = 'cart:items_read';
}
