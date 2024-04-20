<?php

declare(strict_types=1);

namespace App\Utils;

class TimeDiff
{
    public static function minutes(
        \DateTimeInterface $first,
        ?\DateTimeInterface $second = null,
    ): int {
        return intval(self::diff($first, $second) / 60);
    }

    public static function hours(
        \DateTimeInterface $first,
        ?\DateTimeInterface $second = null,
    ): int {
        return intval(self::diff($first, $second) / 3600);
    }

    public static function days(
        \DateTimeInterface $first,
        ?\DateTimeInterface $second = null,
    ): int {
        return intval(self::diff($first, $second) / 86400);
    }

    private static function diff($a, $b = null): int
    {
        if (null === $b) {
            $b = $a;
            $a = new \DateTimeImmutable();
        }

        return intval(abs($a->getTimestamp() - $b->getTimestamp()));
    }
}
