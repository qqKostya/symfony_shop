<?php

declare(strict_types=1);

namespace App\Cart\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RequestItem
{
    #[Assert\NotBlank(message: 'ID товара обязательнл')]
    public int $productId;

    #[Assert\NotBlank(message: 'Количество товара обязательно')]
    public int $quantity;

    public function __construct(int $productId, int $quantity)
    {
        $this->productId = $productId;
        $this->quantity  = $quantity;
    }
}
