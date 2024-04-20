<?php

namespace App\Contract\Service;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\DataObject\Workspace;
use Symfony\Component\Security\Core\User\UserInterface;

interface NexusInterface
{
    public function isAdmin(
        UserInterface $user,
    ): bool;

    public function isWorkspaceOwner(
        Workspace $workspace,
        UserInterface $user,
    ): bool;

    public function isLinkedToProvider(
        Provider $provider,
        UserInterface $user,
    ): bool;

    public function isSpaceOwner(
        Space $space,
        UserInterface $user,
    ): bool;

    public function isOwnerOfSpaceSet(
        SpaceSet $spaceSet,
        UserInterface $user,
    ): bool;

    public function isBookingOwner(
        Booking $booking,
        UserInterface $user,
    ): bool;

    public function isBookingRuleOwner(
        BookingRule $bookingRule,
        UserInterface $user,
    ): bool;
}
