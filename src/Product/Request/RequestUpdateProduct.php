<?php

declare(strict_types=1);

namespace App\Product\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RequestUpdateProduct
{
    #[Assert\Length(min: 2, max: 255, minMessage: 'Имя продукта должно содержать минимум {{ limit }} символов')]
    public string $name;

    #[Assert\Length(max: 500, maxMessage: 'Описание не может быть длиннее {{ limit }} символов')]
    public ?string $description;

    #[Assert\PositiveOrZero(message: 'Цена не может быть отрицательной')]
    public ?int $cost;

    #[Assert\PositiveOrZero(message: 'Налог не может быть отрицательным')]
    public ?int $tax;

    #[Assert\PositiveOrZero(message: 'Вес не может быть отрицательным')]
    public ?int $weight;

    #[Assert\PositiveOrZero(message: 'Высота не может быть отрицательной')]
    public ?int $height;

    #[Assert\PositiveOrZero(message: 'Ширина не может быть отрицательной')]
    public ?int $width;

    #[Assert\PositiveOrZero(message: 'Длина не может быть отрицательной')]
    public ?int $length;

    public function __construct(
        string $name,
        ?string $description = null,
        ?int $cost = null,
        ?int $tax = null,
        ?int $weight = null,
        ?int $height = null,
        ?int $width = null,
        ?int $length = null,
    ) {
        $this->name        = $name;
        $this->description = $description;
        $this->cost        = $cost;
        $this->tax         = $tax;
        $this->weight      = $weight;
        $this->height      = $height;
        $this->width       = $width;
        $this->length      = $length;
    }
}
