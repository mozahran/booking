<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Contract\DataObject\RuleInterface;
use App\Domain\Enum\RuleType;

final class Deny implements Normalizable, Denormalizable, RuleInterface
{
    /**
     * @param ConditionGroup[] $conditionGroups
     * @param int[]|null       $spaceIds
     */
    public function __construct(
        private readonly int $daysBitmask,
        private readonly int $startMinutes,
        private readonly int $endMinutes,
        private readonly array $conditionGroups,
        private readonly ?array $spaceIds = null,
    ) {
    }

    public function getType(): RuleType
    {
        return RuleType::DENY;
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

    /**
     * @return ConditionGroup[]
     */
    public function getConditionGroups(): array
    {
        return $this->conditionGroups;
    }

    public function getSpaceIds(): ?array
    {
        return $this->spaceIds;
    }

    public function normalize(): array
    {
        $normalizedConditionGroups = [];
        foreach ($this->getConditionGroups() as $conditionGroup) {
            $normalizedConditionGroups[] = $conditionGroup->normalize();
        }

        return [
            'daysBitmask' => $this->getDaysBitmask(),
            'start' => $this->getStartMinutes(),
            'end' => $this->getEndMinutes(),
            'spaceIds' => $this->getSpaceIds(),
            'g' => $normalizedConditionGroups,
        ];
    }

    public static function denormalize(array $data): Denormalizable
    {
        return new self(
            daysBitmask: $data['daysBitmask'],
            startMinutes: $data['startTotalMinutes'],
            endMinutes: $data['endTotalMinutes'],
            conditionGroups: $data['g'],
            spaceIds: $data['spaceIds'],
        );
    }
}
