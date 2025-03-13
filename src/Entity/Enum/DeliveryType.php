<?php

namespace App\Enum;

enum DeliveryType: string
{
    case SELF_DELIVERY = 'самовывоз';
    case COURIER = 'курьер';
}
