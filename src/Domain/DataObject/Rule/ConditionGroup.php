<?php

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Normalizable;

final readonly class ConditionGroup implements Normalizable
{
    /**
     * @param Condition[] $conditions
     */
    public function __construct(
        private array $conditions,
    ) {
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function normalize(): array
    {
        $result = [];
        foreach ($this->getConditions() as $condition) {
            $result[] = $condition->normalize();
        }

        return $result;
    }
}
