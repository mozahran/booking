<?php

declare(strict_types=1);

namespace App\Contract\DataObject;

interface Normalizable
{
    public function normalize(): array;
}
