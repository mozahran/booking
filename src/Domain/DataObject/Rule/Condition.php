<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Domain\Enum\Rule\Operand;
use App\Domain\Enum\Rule\Operator;

final readonly class Condition implements Normalizable, Denormalizable
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

    public static function denormalize(
        array $data,
    ): self {
        return new self(
            operand: Operand::from($data['operand']),
            operator: Operator::from($data['operator']),
            value: $data['value'],
        );
    }
}
