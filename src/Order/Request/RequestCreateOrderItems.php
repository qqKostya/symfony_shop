<?php

declare(strict_types=1);

namespace App\Order\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RequestCreateOrderItems
{
    #[Assert\NotBlank(message: 'Идентификатор продукта обязателен')]
    #[Assert\Positive(message: 'Идентификатор продукта должен быть положительным числом')]
    public int $productId;

    #[Assert\NotBlank(message: 'Количество товара обязательно')]
    #[Assert\Positive(message: 'Количество товара должно быть положительным числом')]
    public int $quantity;

    public function __construct(int $productId, int $quantity)
    {
        $this->productId = $productId;
        $this->quantity  = $quantity;
    }
}
