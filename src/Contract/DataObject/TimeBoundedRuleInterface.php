<?php

namespace App\Contract\DataObject;

interface TimeBoundedRuleInterface
{
    public function getDaysBitmask(): int;

    public function getStartMinutes(): int;

    public function getEndMinutes(): int;
}