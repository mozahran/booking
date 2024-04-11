<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Normalizable;
use App\Domain\Enum\Rule\Operand;
use App\Domain\Enum\Rule\Operator;

final class Condition implements Normalizable
{
    public function __construct(
        private readonly Operand $Operand,
        private readonly Operator $operator,
        private readonly int $value,
    ) {
    }

    public function getOperand(): Operand
    {
        return $this->Operand;
    }

    public function getOperator(): Operator
    {
        return $this->operator;
    }

    public function getValue(): int
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
