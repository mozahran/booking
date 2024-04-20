<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Repository\ProviderUserDataRepositoryInterface;
use App\Contract\Resolver\ProviderResolverInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Space;
use App\Domain\DataObject\Workspace;
use App\Domain\Enum\UserRole;
use App\Domain\Exception\ProviderUserDataNotFoundException;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\UserEntity;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class Nexus implements NexusInterface
{
    public function __construct(
        private WorkspaceResolverInterface $workspaceResolver,
        private ProviderResolverInterface $providerResolver,
        private ProviderUserDataRepositoryInterface $providerUserDataRepository,
    ) {
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
        UserInterface|UserEntity $user,
    ): bool {
        $providerSet = $this->providerResolver->resolveManyByUser($user->getId());

        return in_array(
            needle: $workspace->getProviderId(),
            haystack: $providerSet->ids(),
            strict: true,
        );
    }

    public function isLinkedToProvider(
        Provider $provider,
        UserInterface $user,
    ): bool {
        $providerIds = $this->getProviderIdsWhereUserIsLinkedTo(user: $user);

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
            $providerUserData = $this->providerUserDataRepository->findOne(
                userId: $user->getId(),
                providerId: $workspace->getProviderId(),
            );
        } catch (WorkspaceNotFoundException|ProviderUserDataNotFoundException) {
            return false;
        }

        return UserRole::OWNER === $providerUserData->getRole();
    }

    public function isOwnerOfSpaceSet(
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
        $providerUserData = $this->providerUserDataRepository->findManyByUser(userId: $user->getId())->items();
        foreach ($providerUserData as $providerUserDatum) {
            if (UserRole::OWNER !== $providerUserDatum->getRole()) {
                continue;
            }
            $providerIds[] = $providerUserDatum->getProviderId();
        }

        return $providerIds;
    }

    /**
     * @return int[];
     */
    private function getProviderIdsWhereUserIsLinkedTo(
        UserInterface $user,
    ): array {
        $providerIds = [];
        $providerUserData = $this->providerUserDataRepository->findManyByUser(userId: $user->getId());
        $providerUserData = $providerUserData->items();
        foreach ($providerUserData as $providerUserDatum) {
            $providerIds[] = $providerUserDatum->getProviderId();
        }

        return $providerIds;
    }

    public function isBookingRuleOwner(
        BookingRule $bookingRule,
        UserInterface $user,
    ): bool {
        $provider = $this->providerResolver->resolve(id: $bookingRule->getWorkspaceId());
        try {
            $providerUserData = $this->providerUserDataRepository->findOne(
                userId: $user->getId(),
                providerId: $provider->getId(),
            );
        } catch (ProviderUserDataNotFoundException) {
            return false;
        }

        return UserRole::OWNER === $providerUserData->getRole();
    }
}
