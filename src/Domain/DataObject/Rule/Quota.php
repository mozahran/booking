<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Contract\DataObject\RuleInterface;
use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Domain\Enum\Rule\Period;
use App\Domain\Enum\RuleType;

final class Quota implements Normalizable, Denormalizable, RuleInterface, TimeBoundedRuleInterface
{
    /**
     * @param string[]|null $roles
     * @param int[]|null    $spaceIds
     */
    public function __construct(
        private readonly int $daysBitmask,
        private readonly int $startMinutes,
        private readonly int $endMinutes,
        private readonly int $value,
        private readonly Period $period,
        private readonly ?array $roles = null,
        private readonly ?array $spaceIds = null,
    ) {
    }

    public function getType(): RuleType
    {
        return RuleType::QUOTA;
    }

    public function getDaysBitmask(): int
    {
        return $this->daysBitmask;
    }

    public function getStartMinutes(): int
    {
        return $this->startMinutes;
    }

    public function getEndMinutes(): int
    {
        return $this->endMinutes;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getPeriod(): Period
    {
        return $this->period;
    }

    /**
     * @return int[]|null
     */
    public function getSpaceIds(): ?array
    {
        return $this->spaceIds;
    }

    /**
     * @return string[]|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function normalize(): array
    {
        return [
            'daysBitmask' => $this->getDaysBitmask(),
            'start' => $this->getStartMinutes(),
            'end' => $this->getEndMinutes(),
            'value' => $this->getValue(),
            'period' => $this->getPeriod()->value,
            'roles' => $this->getRoles(),
            'spaceIds' => $this->getSpaceIds(),
        ];
    }

    public static function denormalize(array $data): self
    {
        return new self(
            daysBitmask: $data['daysBitmask'],
            startMinutes: $data['start'],
            endMinutes: $data['end'],
            value: $data['value'],
            period: $data['period'],
            roles: $data['roles'],
            spaceIds: $data['spaceIds'],
        );
    }
}
