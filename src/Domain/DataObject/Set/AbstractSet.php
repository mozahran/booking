<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Contract\DataObject\Identifiable;
use App\Contract\DataObject\Normalizable;

abstract class AbstractSet implements Normalizable
{
    public function __construct(
        protected array $items = [],
    ) {
    }

    public function add(mixed $item): void
    {
        $this->items[] = $item;
    }

    public function remove(mixed $item): void
    {
        foreach ($this->items as $index => $i) {
            if ($i === $item) {
                unset($this->items[$index]);
                break;
            }
        }
    }

    public function items(): array
    {
        return $this->items;
    }

    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    public function last(): mixed
    {
        $i = $this->count() - 1;

        return $this->items[$i] ?? null;
    }

    /**
     * @return int[]
     */
    public function ids(): array
    {
        $result = [];

        foreach ($this->items as $item) {
            if (!$item instanceof Identifiable) {
                continue;
            }
            $result[] = $item->getId();
        }

        return $result;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return !$this->count();
    }

    public function normalize(): array
    {
        $result = [];
        foreach ($this->items() as $item) {
            if ($item instanceof Normalizable) {
                $result[] = $item->normalize();
            }
        }

        return $result;
    }
}
