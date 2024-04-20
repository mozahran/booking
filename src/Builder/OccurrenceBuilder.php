<?php

namespace App\Builder;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\OccurrenceProxyMap;
use App\Domain\DataObject\Booking\OccurrenceProxyMapBuilder;
use App\Domain\DataObject\Booking\Status;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\InvalidTimeRangeException;

readonly class OccurrenceBuilder
{
    protected OccurrenceProxyMapBuilder $proxyMapBuilder;

    public function __construct(
        protected OccurrenceProxyMap $existingOccurrences,
    ) {
        $this->proxyMapBuilder = new OccurrenceProxyMapBuilder();
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function add(
        string $startsAt,
        string $endsAt,
        bool $cancelled = false,
        ?int $cancellerId = null,
        ?int $bookingId = null,
        ?int $id = null,
    ): void {
        $occurrence = $this->createOccurrence(
            startsAt: $startsAt,
            endsAt: $endsAt,
            cancelled: $cancelled,
            cancellerId: $cancellerId,
            bookingId: $bookingId,
            id: $id,
        );
        $this->proxyMapBuilder->add(
            occurrence: $occurrence,
        );
    }

    /**
     * @throws InvalidTimeRangeException
     */
    protected function createOccurrence(
        string $startsAt,
        string $endsAt,
        bool $cancelled,
        ?int $cancellerId = null,
        ?int $bookingId = null,
        ?int $id = null,
    ): Occurrence {
        $timeRange = new TimeRange(
            startsAt: $startsAt,
            endsAt: $endsAt,
        );
        /** @var Occurrence $existingOccurrence */
        $existingOccurrence = $this->existingOccurrences->findFor(
            dateTimeString: $timeRange->getDateTimeString(),
        );
        $status = new Status(
            cancelled: $existingOccurrence?->getStatus()->isCancelled() ?? $cancelled,
            cancellerId: $existingOccurrence?->getStatus()->getCancellerId() ?? $cancellerId,
        );

        return new Occurrence(
            timeRange: $timeRange,
            status: $status,
            bookingId: $existingOccurrence?->getBookingId() ?? $bookingId,
            id: $existingOccurrence?->getId() ?? $id,
        );
    }

    public function build(): OccurrenceProxyMap
    {
        $existingOccurrences = $this->existingOccurrences->items();
        foreach ($existingOccurrences as $occurrence) {
            $this->proxyMapBuilder->add(
                occurrence: $occurrence,
            );
        }

        return $this->proxyMapBuilder->build();
    }
}
