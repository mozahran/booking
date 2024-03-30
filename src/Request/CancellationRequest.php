<?php

namespace App\Request;

use App\Domain\Enum\CancellationIntent;
use App\Domain\Exception\InvalidIntentException;

class CancellationRequest extends AbstractRequest
{
    /**
     * @throws InvalidIntentException
     */
    public function getIntent(): CancellationIntent
    {
        $type = $this->request()->getPayload()->getString('intent');
        $intent = CancellationIntent::tryFrom($type);
        if (null === $intent) {
            throw new InvalidIntentException($type);
        }

        return $intent;
    }

    public function getBookingIds(): array
    {
        $ids = $this->request()->getPayload()->all('bookings');

        return array_map('intval', $ids);
    }

    public function getOccurrenceIds(): array
    {
        $ids = $this->request()->getPayload()->all('occurrences');

        return array_map('intval', $ids);
    }
}
