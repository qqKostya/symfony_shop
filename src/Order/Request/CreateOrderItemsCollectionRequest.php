<?php

declare(strict_types=1);

namespace App\Order\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrderItemsCollectionRequest implements \IteratorAggregate
{
    /** @var CreateOrderItemsRequest[] */
    #[Assert\NotBlank(message: 'Товары в заказе обязательны')]
    #[Assert\Valid]
    private array $items = [];

    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof CreateOrderItemsRequest) {
                throw new \InvalidArgumentException('Элемент должен быть объектом CreateOrderItemsRequest.');
            }
        }
        $this->items = $items;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function first(): ?CreateOrderItemsRequest
    {
        return $this->items[0] ?? null;
    }

    public function getByIndex(int $index): ?CreateOrderItemsRequest
    {
        return $this->items[$index] ?? null;
    }

    public function all(): array
    {
        return $this->items;
    }
}
