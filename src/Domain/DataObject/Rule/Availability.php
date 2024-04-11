<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Contract\DataObject\RuleInterface;
use App\Domain\Enum\RuleType;

final class Availability implements Normalizable, Denormalizable, RuleInterface
{
    /**
     * @param int[]|null $spaceIds
     */
    public function __construct(
        private readonly int $daysBitmask,
        private readonly int $startMinutes,
        private readonly int $endMinutes,
        private readonly ?array $spaceIds = null,
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