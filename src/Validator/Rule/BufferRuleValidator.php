<?php

declare(strict_types=1);

namespace App\Validator\Rule;

use App\Builder\TimeRangeExtender;
use App\Contract\DataObject\RuleInterface;
use App\Contract\Repository\BookingRepositoryInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Rule\Buffer;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\DataObject\Set\TimeRangeSet;
use App\Domain\Exception\AppException;
use App\Domain\Exception\InvalidTimeRangeException;
use App\Domain\Exception\RuleViolationException;
use App\Utils\RuleViolationList;

final readonly class BufferRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
    ) {
    }

    /**
     * @throws InvalidTimeRangeException
     * @throws AppException
     */
    public function validate(
        Booking $booking,
        Buffer|RuleInterface $rule,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::empty();
        $minutes = $rule->getValue();
        $conflicts = $this->bookingRepository->countBufferConflicts(
            spaceId: $booking->getSpaceId(),
            timeRangeSet: $this->createExtendedTimeRanges(
                occurrenceSet: $booking->getOccurrences(),
                minutes: $minutes,
            ),
        );

        // TODO: create a RuleViolationException for each conflicting occurrences
        if ($conflicts > 0) {
            $ruleViolationList->add(
                violation: RuleViolationException::buffer($minutes),
            );
        }

        return $ruleViolationList;
    }

    /**
     * @throws InvalidTimeRangeException
     * @throws AppException
     */
    private function createExtendedTimeRanges(
        OccurrenceSet $occurrenceSet,
        int $minutes,
    ): TimeRangeSet {
        $set = new TimeRangeSet();
        $occurrences = $occurrenceSet->items();
        foreach ($occurrences as $occurrence) {
            $timeRange = (new TimeRangeExtender())
                ->setTimeRange($occurrence->getTimeRange())
                ->setMinutes($minutes)
                ->build();
            $set->add($timeRange);
        }

        return $set;
    }
}
