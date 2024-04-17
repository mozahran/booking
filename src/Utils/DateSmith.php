<?php

namespace App\Utils;

use DateTimeImmutable;

final class DateSmith
{
    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public static function withTime(
        int $hour,
        int $minute
    ): DateTimeImmutable {
        return new DateTimeImmutable(sprintf('today %d:%d', $hour, $minute));
    }

    public static function withDateAndTime(
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute
    ): DateTimeImmutable {
        return new DateTimeImmutable(sprintf('%d-%d-%d %d:%d', $year, $month, $day, $hour, $minute));
    }
}