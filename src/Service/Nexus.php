<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Repository\ProviderUserDataRepositoryInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\DataObject\Workspace;
use App\Domain\Enum\UserRole;
use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\ProviderUserDataNotFoundException;
use App\Domain\Exception\SpaceNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class Nexus implements NexusInterface
{
    public function __construct(
        private WorkspaceResolverInterface $workspaceResolver,
        private ProviderUserDataRepositoryInterface $providerUserDataRepository,
        private SpaceResolverInterface $spaceResolver,
    ) {
    }

    /**
     * @throws AccessDeniedException
     */
    public function denyUnlessCanAccessSpace(
        Space $space,
        UserInterface $user,
    ): void {
        try {
            $workspace = $this->workspaceResolver->resolve($space->getWorkspaceId());
            $providerUserData = $this->providerUserDataRepository->findOne(
                userId: $user->getId(),
                providerId: $workspace->getProviderId(),
            );

            if ($providerUserData || $this->isAdmin(user: $user)) {
                return;
            }
        } catch (WorkspaceNotFoundException|ProviderUserDataNotFoundException) {
        }

        throw new AccessDeniedException();
    }

    public function denyUnlessCanManageBooking(
        Booking $booking,
        UserInterface $user,
    ): void {
        $accessDeniedException = new AccessDeniedException(
            'You are not allowed to modify bookings that do not belong to you!',
        );

        if (
            $this->isBookingOwner(booking: $booking, user: $user)
            || $this->isAdmin(user: $user)
        ) {
            return;
        }

        try {
            $space = $this->spaceResolver->resolve($booking->getSpaceId());
            if ($this->isSpaceOwner(space: $space, user: $user)) {
                return;
            }
        } catch (SpaceNotFoundException) {
        }

        throw $accessDeniedException;
    }

    public function denyUnlessCanManageSpace(
        Space $space,
        UserInterface $user,
    ): void {
        try {
            $workspace = $this->workspaceResolver->resolve(id: $space->getWorkspaceId());
            if (
                $this->isWorkspaceOwner(workspace: $workspace, user: $user)
                || $this->isAdmin(user: $user)
            ) {
                return;
            }
        } catch (WorkspaceNotFoundException) {
        }

        throw new AccessDeniedException();
    }

    public function denyUnlessCanManageProvider(
        Provider $provider,
        UserInterface $user,
    ): void {
        $canManageProvider = in_array(
            $provider->getId(),
            $this->getProviderIdsWhereUserIsOwner($user),
            true,
        );

        if ($canManageProvider) {
            return;
        }

        if ($this->isAdmin(user: $user)) {
            return;
        }

        throw new AccessDeniedException();
    }

    public function isAdmin(
        UserInterface $user,
    ): bool {
        return in_array(
            needle: UserRole::ADMIN->value,
            haystack: $user->getRoles(),
            strict: true,
        );
    }

    public function isWorkspaceOwner(
        Workspace $workspace,
        UserInterface $user,
    ): bool {
        $providerIds = $this->getProviderIdsWhereUserIsOwner(user: $user);

        return in_array($workspace->getProviderId(), $providerIds, true);
    }

    public function isLinkedToProvider(
        Provider $provider,
        UserInterface $user,
    ): bool {
        $providerIds = $this->getProviderIdsWhereUserIsOwner(user: $user);

        return in_array(
            needle: $provider->getId(),
            haystack: $providerIds,
            strict: true,
        );
    }

    public function isSpaceOwner(
        Space $space,
        UserInterface $user,
    ): bool {
        try {
            $workspace = $this->workspaceResolver->resolve(id: $space->getWorkspaceId());
        } catch (WorkspaceNotFoundException) {
            return false;
        }

        $providerIds = $this->getProviderIdsWhereUserIsOwner(user: $user);

        return in_array(
            needle: $workspace->getProviderId(),
            haystack: $providerIds,
            strict: true,
        );
    }

    public function isSpacesOwner(
        SpaceSet $spaceSet,
        UserInterface $user,
    ): bool {
        $workspaceSet = $this->workspaceResolver->resolveMany(
            ids: $spaceSet->workspaceIds(),
        );
        $workspaceProviderIds = $workspaceSet->providerIds();
        $providerIds = $this->getProviderIdsWhereUserIsOwner(user: $user);

        return !count(array_diff($workspaceProviderIds, $providerIds));
    }

    public function isBookingOwner(
        Booking $booking,
        UserInterface $user,
    ): bool {
        return $booking->getUserId() === $user->getId();
    }

    /**
     * @return int[];
     */
    private function getProviderIdsWhereUserIsOwner(
        UserInterface $user,
    ): array {
        $providerIds = [];
        $providerUserDataSet = $this->providerUserDataRepository->findManyByUser(userId: $user->getId());
        $providerUserDataItems = $providerUserDataSet->items();
        foreach ($providerUserDataItems as $providerUserData) {
            if (UserRole::OWNER !== $providerUserData->getRole()) {
                continue;
            }
            $providerIds[] = $providerUserData->getProviderId();
        }

        return $providerIds;
    }
}
