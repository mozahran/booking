<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Contract\DataObject\RuleInterface;
use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Domain\Enum\Rule\AggregationMetric;
use App\Domain\Enum\Rule\Mode;
use App\Domain\Enum\Rule\Period;
use App\Domain\Enum\RuleType;

final readonly class Quota implements Normalizable, Denormalizable, RuleInterface, TimeBoundedRuleInterface
{
    /**
     * @param string[]|null $roles
     * @param int[]|null    $spaceIds
     */
    public function __construct(
        private int $daysBitmask,
        private int $startMinutes,
        private int $endMinutes,
        private int $value,
        private AggregationMetric $aggregationMetric,
        private Mode $mode,
        private Period $period,
        private ?array $roles = null,
        private ?array $spaceIds = null,
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

    public function getAggregationMetric(): AggregationMetric
    {
        return $this->aggregationMetric;
    }

    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function getPeriod(): Period
    {
        return $this->period;
    }

    /**
     * @return ?int[]
     */
    public function getSpaceIds(): ?array
    {
        return $this->spaceIds;
    }

    /**
     * @return ?string[]
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
            'aggregationMetric' => $this->getAggregationMetric()->value,
            'mode' => $this->getMode()->value,
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
            aggregationMetric: AggregationMetric::from($data['aggregationMetric']),
            mode: Mode::from($data['mode']),
            period: Period::from($data['period']),
            roles: $data['roles'],
            spaceIds: $data['spaceIds'],
        );
    }
}
