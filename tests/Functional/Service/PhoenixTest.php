<?php

namespace App\Tests\Functional\Service;

use App\Contract\Repository\ProviderRepositoryInterface;
use App\Contract\Repository\SpaceRepositoryInterface;
use App\Contract\Repository\UserRepositoryInterface;
use App\Contract\Repository\WorkspaceRepositoryInterface;
use App\Contract\Service\PhoenixInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PhoenixTest extends KernelTestCase
{
    private PhoenixInterface $phoenix;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phoenix = $this->getContainer()->get(PhoenixInterface::class);
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testDeactivateUser()
    {
        $userId = 1;
        $userRepository = $this->getUserRepository();
        $user = $userRepository->findOne($userId);
        $this->phoenix->deactivateUser($user);
        $this->entityManager->clear();
        $user = $userRepository->findOne($userId);

        $this->assertFalse($user->isActive());
    }

    public function testActivateUser()
    {
        $userId = 1;
        $userRepository = $this->getUserRepository();
        $user = $userRepository->findOne($userId);
        $this->phoenix->activateUser($user);
        $this->entityManager->clear();
        $user = $userRepository->findOne($userId);

        $this->assertTrue($user->isActive());
    }

    public function testDeactivateWorkspace()
    {
        $workspaceId = 1;
        $workspaceRepository = $this->getWorkspaceRepository();
        $workspace = $workspaceRepository->findOne($workspaceId);
        $this->phoenix->deactivateWorkspace($workspace);
        $this->entityManager->clear();
        $workspace = $workspaceRepository->findOne($workspaceId);

        $this->assertFalse($workspace->isActive());
    }

    public function testActivateWorkspace()
    {
        $workspaceId = 1;
        $workspaceRepository = $this->getWorkspaceRepository();
        $workspace = $workspaceRepository->findOne($workspaceId);
        $this->phoenix->activateWorkspace($workspace);
        $this->entityManager->clear();
        $workspace = $workspaceRepository->findOne($workspaceId);

        $this->assertTrue($workspace->isActive());
    }

    public function testDeactivateSpace()
    {
        $spaceId = 1;
        $spaceRepository = $this->getSpaceRepository();
        $space = $spaceRepository->findOne($spaceId);
        $this->phoenix->deactivateSpace($space);
        $this->entityManager->clear();
        $space = $spaceRepository->findOne($spaceId);

        $this->assertFalse($space->isActive());
    }

    public function testActivateSpace()
    {
        $spaceId = 1;
        $spaceRepository = $this->getSpaceRepository();
        $space = $spaceRepository->findOne($spaceId);
        $this->phoenix->activateSpace($space);
        $this->entityManager->clear();
        $space = $spaceRepository->findOne($spaceId);

        $this->assertTrue($space->isActive());
    }

    public function testDeactivateProvider()
    {
        $providerId = 1;
        $providerRepository = $this->getProviderRepository();
        $provider = $providerRepository->findOne($providerId);
        $this->phoenix->deactivateProvider($provider);
        $this->entityManager->clear();
        $provider = $providerRepository->findOne($providerId);

        $this->assertFalse($provider->isActive());
    }

    public function testActivateProvider()
    {
        $providerId = 1;
        $providerRepository = $this->getProviderRepository();
        $provider = $providerRepository->findOne($providerId);
        $this->phoenix->activateProvider($provider);
        $this->entityManager->clear();
        $provider = $providerRepository->findOne($providerId);

        $this->assertTrue($provider->isActive());
    }

    private function getProviderRepository(): ProviderRepositoryInterface
    {
        /** @var ProviderRepositoryInterface $repository */
        $repository = $this->getContainer()->get(ProviderRepositoryInterface::class);

        return $repository;
    }

    private function getSpaceRepository(): SpaceRepositoryInterface
    {
        /** @var SpaceRepositoryInterface $repository */
        $repository = $this->getContainer()->get(SpaceRepositoryInterface::class);

        return $repository;
    }

    private function getWorkspaceRepository(): WorkspaceRepositoryInterface
    {
        /** @var WorkspaceRepositoryInterface $repository */
        $repository = $this->getContainer()->get(WorkspaceRepositoryInterface::class);

        return $repository;
    }

    private function getUserRepository(): UserRepositoryInterface
    {
        /** @var UserRepositoryInterface $repository */
        $repository = $this->getContainer()->get(UserRepositoryInterface::class);

        return $repository;
    }
}
