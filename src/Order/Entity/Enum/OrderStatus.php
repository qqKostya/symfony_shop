<?php

namespace App\Order\Entity\Enum;

enum OrderStatus: string
{
    case PAID = 'оплачен';
    case ASSEMBLING = 'в сборке';
    case READY = 'готов к выдаче';
    case DELIVERING = 'доставляется';
    case RECEIVED = 'получен';
    case CANCELED = 'отменён';
}
