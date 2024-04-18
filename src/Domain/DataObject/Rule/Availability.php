<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Contract\DataObject\RuleInterface;
use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Domain\Enum\RuleType;

final readonly class Availability implements Normalizable, Denormalizable, RuleInterface, TimeBoundedRuleInterface
{
    /**
     * @param int[]|null $spaceIds
     */
    public function __construct(
        private int $daysBitmask,
        private int $startMinutes,
        private int $endMinutes,
        private ?array $spaceIds = null,
    ) {
    }

    public function getType(): RuleType
    {
        return RuleType::AVAILABILITY;
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
     * @return int[]|null
     */
    public function getSpaceIds(): ?array
    {
        return $this->spaceIds;
    }

    public function normalize(): array
    {
        return [
            'daysBitmask' => $this->getDaysBitmask(),
            'start' => $this->getStartMinutes(),
            'end' => $this->getEndMinutes(),
            'spaceIds' => $this->getSpaceIds(),
        ];
    }

    public static function denormalize(array $data): self
    {
        return new self(
            daysBitmask: intval($data['daysBitmask']),
            startMinutes: intval($data['start']),
            endMinutes: intval($data['end']),
            spaceIds: $data['spaceIds'],
        );
    }
}
