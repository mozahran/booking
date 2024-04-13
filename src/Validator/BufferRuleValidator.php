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

final class BufferRuleValidator implements RuleValidatorInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
    ) {
    }

    public function validate(
        Booking $booking,
        Buffer|RuleInterface $rule,
    ): array {
        if ($this->shouldIgnoreValidator($rule, $booking)) {
            return [];
        }

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

        if ($bufferConflicts > 0) {
            return [
                RuleViolationException::buffer($value),
            ];
        }

        return [];
    }

    private function shouldIgnoreValidator(
        Buffer $rule,
        Booking $booking,
    ): bool {
        $spaceId = $booking->getSpaceId();
        $targetSpaceIds = $rule->getSpaceIds();

        return is_array($targetSpaceIds) && !in_array($spaceId, $targetSpaceIds);
    }

    /**
     * @throws InvalidTimeRangeException
     */
    private function createExtendedTimeRangeFor(
        Occurrence $occurrence,
        int $value
    ): TimeRange {
        $occurrenceTimeRange = $occurrence->getTimeRange();
        $occurrenceStartsAt = $occurrenceTimeRange->getStartsAt();
        $occurrenceEndsAt = $occurrenceTimeRange->getEndsAt();
        // Extend booking time by the given value from both ends
        $startsAt = \DateTime::createFromImmutable($occurrenceStartsAt)->modify(sprintf('-%d minutes', $value));
        $endsAt = \DateTime::createFromImmutable($occurrenceEndsAt)->modify(sprintf('+%d minutes', $value));

        return new TimeRange(
            startsAt: $startsAt->format(\DateTimeInterface::ATOM),
            endsAt: $endsAt->format(\DateTimeInterface::ATOM),
        );
    }
}
