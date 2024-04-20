<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Contract\DataObject\RuleInterface;
use App\Domain\Enum\Rule\Mode;
use App\Domain\Enum\RuleType;

final readonly class Repeat implements Normalizable, Denormalizable, RuleInterface
{
    public function __construct(
        private Mode $mode,
        private ?array $roles = null,
        private ?array $spaceIds = null,
    ) {
    }

    public function getType(): RuleType
    {
        return RuleType::REPEAT;
    }

    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function getSpaceIds(): ?array
    {
        return $this->spaceIds;
    }

    public static function denormalize(array $data): Denormalizable
    {
        return new self(
            mode: Mode::from($data['mode']),
            roles: $data['roles'],
            spaceIds: $data['spaceIds'],
        );
    }

    public function normalize(): array
    {
        return [
            'mode' => $this->getMode()->value,
            'roles' => $this->getRoles(),
            'spaceIds' => $this->getSpaceIds(),
        ];
    }
}
