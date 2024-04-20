<?php

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;

final readonly class ConditionGroup implements Normalizable, Denormalizable
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
        $normalized = [];
        foreach ($this->getConditions() as $condition) {
            $normalized[] = $condition->normalize();
        }

        return [
            'conditions' => $normalized,
        ];
    }

    public static function denormalize(
        array $data,
    ): Denormalizable {
        $conditions = [];
        $normalizedConditions = $data['conditions'];
        foreach ($normalizedConditions as $conditionData) {
            $conditions[] = Condition::denormalize($conditionData);
        }

        return new self(
            conditions: $conditions,
        );
    }
}
