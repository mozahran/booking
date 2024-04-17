<?php

declare(strict_types=1);

namespace App\Utils;

use App\Domain\Enum\Rule\Operator;

class Comparator
{
    public static function is(
        mixed $x,
        Operator $operator,
        mixed $y,
    ): bool {
        return match ($operator) {
            Operator::LESS_THAN => $x < $y,
            Operator::LESS_THAN_OR_EQUAL_TO => $x <= $y,
            Operator::GREATER_THAN => $x > $y,
            Operator::GREATER_THAN_OR_EQUAL_TO => $x >= $y,
            Operator::EQUAL_TO => $x === $y,
            Operator::NOT_EQUAL_TO => $x !== $y,
            Operator::NOT_MULTIPLE_OF => $y % $x === 0,
            Operator::IS_MULTIPLE_OF => $y % $x !== 0,
            Operator::INSET => is_array($x) ? boolval(array_intersect($x, $y)) : in_array($x, $y),
            Operator::NOT_INSET => !in_array($x, $y),
        };
    }
}
