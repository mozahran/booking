<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use Symfony\Component\HttpFoundation\Response;

class TimeSlotNotAvailableException extends AppException
{
    public function __construct(
        OccurrenceSet $occurrenceSet,
    ) {
        $message = '';

        $occurrenceSetItems = $occurrenceSet->items();
        foreach ($occurrenceSetItems as $occurrence) {
            $message .= $this->parseMessage($occurrence);
        }

        parent::__construct(
            message: $message,
            code: Response::HTTP_BAD_REQUEST,
        );
    }

    private function isSameDay(
        TimeRange $timeRange,
    ): bool {
        return $timeRange->getStartsAt()->format('j') === $timeRange->getEndsAt()->format('j');
    }

    private function parseMessage(
        Occurrence $occurrence,
    ): string {
        $timeRange = $occurrence->getTimeRange();
        $isSameDay = $this->isSameDay($timeRange);

        if ($isSameDay) {
            return sprintf(
                'Requested time slot from %s to %s on %s is not available',
                $timeRange->getStartTime(),
                $timeRange->getEndTime(),
                $timeRange->getStartsAt()->format('Y-m-d'),
            );
        }

        return sprintf(
            'Requested time slot from %s to %s is not available',
            $timeRange->getStartsAt()->format(\DateTimeInterface::ATOM),
            $timeRange->getEndsAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
