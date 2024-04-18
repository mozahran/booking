<?php

namespace App\Service;

use App\Contract\Repository\ProviderRepositoryInterface;
use App\Contract\Repository\SpaceRepositoryInterface;
use App\Contract\Repository\UserRepositoryInterface;
use App\Contract\Repository\WorkspaceRepositoryInterface;
use App\Contract\Service\PhoenixInterface;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Space;
use App\Domain\DataObject\User;
use App\Domain\DataObject\Workspace;

final readonly class Phoenix implements PhoenixInterface
{
    public function __construct(
        private ProviderRepositoryInterface $providerRepository,
        private SpaceRepositoryInterface $spaceRepository,
        private WorkspaceRepositoryInterface $workspaceRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function activateProvider(
        Provider $provider,
    ): void {
        $this->providerRepository->activate(id: $provider->getId());
    }

    public function deactivateProvider(
        Provider $provider,
    ): void {
        $this->providerRepository->deactivate(id: $provider->getId());
    }

    public function activateSpace(
        Space $space,
    ): void {
        $this->spaceRepository->activate(id: $space->getId());
    }

    public function deactivateSpace(
        Space $space,
    ): void {
        $this->spaceRepository->deactivate(id: $space->getId());
    }

    public function activateWorkspace(
        Workspace $workspace,
    ): void {
        $this->workspaceRepository->activate(id: $workspace->getId());
    }

    public function deactivateWorkspace(
        Workspace $workspace,
    ): void {
        $this->workspaceRepository->deactivate(id: $workspace->getId());
    }

    public function activateUser(
        User $user,
    ): void {
        $this->userRepository->activate(id: $user->getId());
    }

    public function deactivateUser(
        User $user,
    ): void {
        $this->userRepository->deactivate(id: $user->getId());
    }
}
