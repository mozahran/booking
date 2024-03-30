<?php

declare(strict_types=1);

namespace App\Security;

use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Domain\Exception\BookingNotFoundException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Entity\UserEntity;
use App\Request\BookingRequest;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BookingVoter extends Voter
{
    public const MANAGE = 'MANAGE_BOOKING';

    public function __construct(
        private readonly NexusInterface $nexus,
        private readonly BookingResolverInterface $bookingResolver,
        private readonly SpaceResolverInterface $spaceResolver,
    ) {
    }

    protected function supports(
        string $attribute,
        mixed $subject,
    ): bool {
        return self::MANAGE === $attribute;
    }

    /**
     * @throws BookingNotFoundException
     * @throws SpaceNotFoundException
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
    ): bool {
        $user = $token->getUser();
        if (!$user instanceof UserEntity) {
            return false;
        }

        if ($this->nexus->isAdmin(user: $user)) {
            return true;
        }

        if (!$subject instanceof BookingRequest) {
            return false;
        }

        $bookingId = $subject->getBookingId();
        $booking = $this->bookingResolver->resolve(id: $bookingId);
        if ($this->nexus->isBookingOwner($booking, $user)) {
            return true;
        }

        $spaceId = $booking->getSpaceId();
        $space = $this->spaceResolver->resolve(id: $spaceId);
        if ($this->nexus->isSpaceOwner($space, $user)) {
            return true;
        }

        return false;
    }
}
