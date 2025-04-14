<?php

declare(strict_types=1);

namespace App\Order\Entity\Enum;

enum OrderStatus: string
{
    use EnumValuesTrait;
    case PAID       = 'оплачен';
    case ASSEMBLING = 'в сборке';
    case READY      = 'готов к выдаче';
    case DELIVERING = 'доставляется';
    case RECEIVED   = 'получен';
    case CANCELED   = 'отменён';
}
