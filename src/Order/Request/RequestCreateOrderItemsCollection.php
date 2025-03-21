<?php

namespace App\Order\Request;

use Symfony\Component\Validator\Constraints as Assert;
use ArrayIterator;
use IteratorAggregate;
use InvalidArgumentException;

final class RequestCreateOrderItemsCollection implements IteratorAggregate
{
    /** @var RequestCreateOrderItems[] */
    #[Assert\NotBlank(message: "Товары в заказе обязательны")]
    #[Assert\Valid]
    private array $items = [];

    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof RequestCreateOrderItems) {
                throw new InvalidArgumentException('Элемент должен быть объектом RequestCreateOrderItems.');
            }
        }
        $this->items = $items;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function first(): ?RequestCreateOrderItems
    {
        return $this->items[0] ?? null;
    }

    public function getByIndex(int $index): ?RequestCreateOrderItems
    {
        return $this->items[$index] ?? null;
    }

    public function all(): array
    {
        return $this->items;
    }
}
