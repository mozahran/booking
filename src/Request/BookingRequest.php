<?php

namespace App\Request;

use App\Contract\Request\TimeRangeAware;
use App\Domain\Exception\AppException;

class BookingRequest extends AbstractRequest implements TimeRangeAware
{
    public function getStartsAt(mixed $default = ''): string
    {
        return $this->request()->get('startsAt')
            ?? $this->request()->getPayload()->get('startsAt', $default);
    }

    public function getEndsAt(mixed $default = ''): string
    {
        return $this->request()->get('endsAt')
            ?? $this->request()->getPayload()->get('endsAt', $default);
    }

    public function getSpaceId(): int
    {
        return (int) $this->request()->get('space', 0)
            ?? $this->request()->getPayload()->get('space', 0);
    }

    public function getBookingId(): int
    {
        return (int) $this->request()->get('bookingId', 0)
            ?? $this->request()->getPayload()->get('bookingId', 0);
    }

    public function getRecurrenceRule(): ?string
    {
        return $this->request()->get('recurrenceRule')
            ?? $this->request()->getPayload()->get('recurrenceRule');
    }

    /**
     * @throws AppException
     */
    public function validate(): void
    {
        if (
            '' === $this->getStartsAt()
            || '' === $this->getEndsAt()
            || 0 === $this->getSpaceId()
        ) {
            throw new AppException('Booking payload is incomplete!');
        }
    }
}
