<?php

declare(strict_types=1);

namespace App\Contract\DataObject;

interface Denormalizable
{
    public static function denormalize(array $data): Denormalizable;
}
