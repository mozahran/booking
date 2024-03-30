<?php

namespace App\Contract\Service;

use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\DataObject\Workspace;
use App\Domain\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

interface NexusInterface
{
    /**
     * @throws AccessDeniedException
     */
    public function denyUnlessCanAccessSpace(
        Space $space,
        UserInterface $user,
    ): void;

    /**
     * @throws AccessDeniedException
     */
    public function denyUnlessCanManageBooking(
        Booking $booking,
        UserInterface $user,
    ): void;

    public function isAdmin(
        UserInterface $user,
    ): bool;

    public function isWorkspaceOwner(
        Workspace $workspace,
        UserInterface $user,
    ): bool;

    /**
     * @throws AccessDeniedException
     */
    public function denyUnlessCanManageSpace(
        Space $space,
        UserInterface $user,
    ): void;

    /**
     * @throws AccessDeniedException
     */
    public function denyUnlessCanManageProvider(
        Provider $provider,
        UserInterface $user,
    ): void;

    public function isLinkedToProvider(
        Provider $provider,
        UserInterface $user,
    ): bool;

    public function isSpaceOwner(
        Space $space,
        UserInterface $user,
    ): bool;

    public function isSpacesOwner(
        SpaceSet $spaceSet,
        UserInterface $user,
    ): bool;
}
