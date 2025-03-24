<?php

namespace App\Order\Request;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

final class RequestDeliveryAddress
{
    #[Assert\NotBlank(message: "КЛАДР обязателен")]
    #[Assert\Type(type: Types::INTEGER, message: "kladrId должен быть числом")]
    public int $kladrId;

    #[Assert\NotBlank(message: "Полный адрес обязателен")]
    #[Assert\Type(type: Types::STRING, message: "fullAddress должен быть строкой")]
    public string $fullAddress;

    public function __construct(?int $kladrId, ?string $fullAddress)
    {
        $this->kladrId = $kladrId;
        $this->fullAddress = $fullAddress;
    }
}
