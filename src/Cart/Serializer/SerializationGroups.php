<?php

declare(strict_types=1);

namespace App\Cart\Serializer;

final class SerializationGroups
{
    public const string CART_READ       = 'cart:read';
    public const string CART_WRITE      = 'cart:write';
    public const string CART_ITEMS_READ = 'cart:items_read';
}
