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

    public static function getValues(): array
    {
        return [
            self::PAID->value,
            self::ASSEMBLING->value,
            self::READY->value,
            self::DELIVERING->value,
            self::RECEIVED->value,
            self::CANCELED->value,
        ];
    }
}
