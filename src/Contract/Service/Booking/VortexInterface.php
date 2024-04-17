<?php

declare(strict_types=1);

namespace App\Contract\Service\Booking;

use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\DataMismatchException;
use Symfony\Component\Security\Core\User\UserInterface;

interface VortexInterface
{
    /**
     * @param int[] $bookingIds
     *
     * @throws AccessDeniedException
     */
    public function cancelBookings(
        array $bookingIds,
        UserInterface $user,
    ): void;

    /**
     * @param int[] $occurrenceIds
     *
     * @throws AccessDeniedException
     * @throws DataMismatchException
     */
    public function cancelOccurrences(
        array $occurrenceIds,
        UserInterface $user,
    ): void;

    /**
     * @throws AccessDeniedException
     * @throws DataMismatchException
     */
    public function cancelSelectedAndFollowingOccurrences(
        int $occurrenceId,
        UserInterface $user,
    ): void;
}
