<?php

namespace App\Order\Entity\Enum;

enum DeliveryType: string
{
    case SELF_DELIVERY = 'самовывоз';
    case COURIER = 'курьер';
}
