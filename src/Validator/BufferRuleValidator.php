<?php

declare(strict_types=1);

namespace App\Validator;

use App\Contract\DataObject\RuleInterface;
use App\Contract\Repository\BookingRepositoryInterface;
use App\Contract\Validator\RuleValidatorInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Rule\Buffer;
use App\Domain\Exception\InvalidTimeRangeException;
use App\Domain\Exception\RuleViolationException;
use App\Utils\RuleViolationList;
use DateTime;

final class BufferRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
    ) {
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function validate(
        Booking $booking,
        Buffer|RuleInterface $rule,
    ): RuleViolationList {
        $ruleViolationList = RuleViolationList::create();
        $value = $rule->getValue();
        $timeRanges = [];
        $occurrences = $booking->getOccurrences()->items();
        foreach ($occurrences as $occurrence) {
            $timeRanges[] = $this->createExtendedTimeRangeFor($occurrence, $value);
        }

        $bufferConflicts = $this->bookingRepository->countBufferConflicts(
            spaceId: $booking->getSpaceId(),
            timeRanges: $timeRanges,
        );

        // TODO: create a RuleViolationException for each conflicting occurrences
        if ($bufferConflicts > 0) {
            $ruleViolationList->add(
                violation: RuleViolationException::buffer($value),
            );
        }

        return $ruleViolationList;
    }

    /**
     * @throws InvalidTimeRangeException
     */
    private function createExtendedTimeRangeFor(
        Occurrence $occurrence,
        int $value
    ): TimeRange {
        $timeRange = $occurrence->getTimeRange();
        $occurrenceStartsAt = $timeRange->getStartsAt();
        $occurrenceEndsAt = $timeRange->getEndsAt();
        // Extend booking time by the given value from both ends
        $startsAt = DateTime::createFromImmutable($occurrenceStartsAt)->modify(sprintf('-%d minutes', $value));
        $endsAt = DateTime::createFromImmutable($occurrenceEndsAt)->modify(sprintf('+%d minutes', $value));

        return new TimeRange(
            startsAt: $startsAt->format(\DateTimeInterface::ATOM),
            endsAt: $endsAt->format(\DateTimeInterface::ATOM),
        );
    }
}
