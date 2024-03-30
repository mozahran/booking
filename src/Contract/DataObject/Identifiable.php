<?php

declare(strict_types=1);

namespace App\Contract\DataObject;

interface Identifiable
{
    public function getId(): ?int;
}
