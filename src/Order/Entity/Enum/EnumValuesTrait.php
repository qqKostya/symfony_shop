<?php

declare(strict_types=1);

namespace App\Order\Entity\Enum;

trait EnumValuesTrait
{
    public static function getValues(): array
    {
        return array_map(static fn(self $case) => $case->value, self::cases());
    }
}
