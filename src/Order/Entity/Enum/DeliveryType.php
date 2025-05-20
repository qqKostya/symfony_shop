<?php

declare(strict_types=1);

namespace App\Order\Entity\Enum;

enum DeliveryType: string
{
    use EnumValuesTrait;
    case SELF_DELIVERY = 'самовывоз';
    case COURIER       = 'курьер';
}
