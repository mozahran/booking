<?php

declare(strict_types=1);

namespace App\Security;

use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Resolver\OccurrenceResolverInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Domain\Enum\CancellationIntent;
use App\Domain\Exception\InvalidIntentException;
use App\Entity\UserEntity;
use App\Request\CancellationRequest;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CancellationVoter extends Voter
{
    public const MANAGE = 'IS_CANCELLER';

    public function __construct(
        private readonly NexusInterface $nexus,
        private readonly BookingResolverInterface $bookingResolver,
        private readonly SpaceResolverInterface $spaceResolver,
        private readonly OccurrenceResolverInterface $occurrenceResolver,
    ) {
    }

    /**
     * @param int[] $ids
     */
    private function canCancelSelectedOccurrences(
        array $ids,
        UserEntity $user,
    ): bool {
        $occurrenceSet = $this->occurrenceResolver->resolveMany(ids: $ids);
        $bookingIds = $occurrenceSet->bookingIds();

        return $this->canCancelSelectedBookings(
            ids: $bookingIds,
            user: $user,
        );
    }

    /**
     * @param int[] $ids
     */
    private function canCancelSelectedBookings(
        array $ids,
        UserEntity $user,
    ): bool {
        $bookingSet = $this->bookingResolver->resolveMany(ids: $ids);

        $ownerIds = $bookingSet->ownerIds();
        if ($this->isOwnerOfSelectedBookings(ownerIds: $ownerIds, userEntity: $user)) {
            return true;
        }

        $spaceSet = $this->spaceResolver->resolveMany(ids: $bookingSet->spaceIds());
        if ($this->nexus->isSpacesOwner(spaceSet: $spaceSet, user: $user)) {
            return true;
        }

        return false;
    }

    private function isOwnerOfSelectedBookings(
        array $ownerIds,
        UserEntity $userEntity,
    ): bool {
        return 1 === count($ownerIds) && isset($ownerIds[$userEntity->getId()]);
    }

    protected function supports(
        string $attribute,
        mixed $subject,
    ): bool {
        return self::MANAGE === $attribute && $subject instanceof CancellationRequest;
    }

    /**
     * @throws InvalidIntentException
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
    ): bool {
        /** @var CancellationRequest $cancellationRequest */
        $cancellationRequest = $subject;
        $user = $token->getUser();
        if (!$user instanceof UserEntity) {
            return false;
        }

        if ($this->nexus->isAdmin(user: $user)) {
            return true;
        }

        if (CancellationIntent::ALL === $cancellationRequest->getIntent()) {
            $selectedBookingIds = $cancellationRequest->getBookingIds();

            return $this->canCancelSelectedBookings(
                ids: $selectedBookingIds,
                user: $user,
            );
        }

        if (CancellationIntent::SELECTED === $cancellationRequest->getIntent()) {
            $selectedOccurrenceIds = $cancellationRequest->getOccurrenceIds();

            return $this->canCancelSelectedOccurrences(
                ids: $selectedOccurrenceIds,
                user: $user,
            );
        }

        if (CancellationIntent::SELECTED_AND_FOLLOWING === $cancellationRequest->getIntent()) {
            $selectedOccurrenceId = current($cancellationRequest->getOccurrenceIds());

            return $this->canCancelSelectedOccurrences(
                ids: [$selectedOccurrenceId],
                user: $user,
            );
        }

        return false;
    }
}
