<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Normalizable;
use App\Domain\Enum\Rule\Operand;
use App\Domain\Enum\Rule\Operator;

final readonly class Condition implements Normalizable
{
    public function __construct(
        private Operand $operand,
        private Operator $operator,
        private mixed $value,
    ) {
    }

    public function getOperand(): Operand
    {
        return $this->operand;
    }

    public function getOperator(): Operator
    {
        return $this->operator;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function normalize(): array
    {
        return [
            'operand' => $this->getOperand()->value,
            'operator' => $this->getOperator()->value,
            'value' => $this->getValue(),
        ];
    }
}
