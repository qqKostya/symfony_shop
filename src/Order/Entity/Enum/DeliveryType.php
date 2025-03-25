<?php

declare(strict_types=1);

namespace App\Order\Entity\Enum;

enum DeliveryType: string
{
    case SELF_DELIVERY = 'самовывоз';
    case COURIER       = 'курьер';

    public static function getValues(): array
    {
        return [
            self::SELF_DELIVERY->value,
            self::COURIER->value,
        ];
    }
}
