<?php

namespace App\Contract\Request;

interface TimeRangeAware
{
    public function getStartsAt(mixed $default = ''): string;

    public function getEndsAt(mixed $default = ''): string;
}
