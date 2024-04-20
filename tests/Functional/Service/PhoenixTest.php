<?php

namespace App\Tests\Functional\Service;

use App\Contract\Repository\ProviderRepositoryInterface;
use App\Contract\Repository\SpaceRepositoryInterface;
use App\Contract\Repository\UserRepositoryInterface;
use App\Contract\Repository\WorkspaceRepositoryInterface;
use App\Contract\Service\PhoenixInterface;
use App\Contract\Translator\ProviderTranslatorInterface;
use App\Contract\Translator\SpaceTranslatorInterface;
use App\Contract\Translator\UserTranslatorInterface;
use App\Contract\Translator\WorkspaceTranslatorInterface;
use App\DataFixtures\Tests\Service\PhoenixFixtures;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Space;
use App\Domain\DataObject\User;
use App\Domain\DataObject\Workspace;
use App\Entity\ProviderEntity;
use App\Entity\SpaceEntity;
use App\Entity\UserEntity;
use App\Entity\WorkspaceEntity;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PhoenixTest extends KernelTestCase
{
    private PhoenixInterface $phoenix;
    private AbstractDatabaseTool $databaseTool;
    private EntityManagerInterface $entityManager;
    private UserTranslatorInterface $userTranslator;
    private WorkspaceTranslatorInterface $workspaceTranslator;
    private SpaceTranslatorInterface $spaceTranslator;
    private ProviderTranslatorInterface $providerTranslator;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var PhoenixInterface $phoenix */
        $phoenix = $this->getContainer()->get(PhoenixInterface::class);
        /** @var AbstractDatabaseTool $databaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        /** @var UserTranslatorInterface $userTranslator */
        $userTranslator = $this->getContainer()->get(UserTranslatorInterface::class);
        /** @var WorkspaceTranslatorInterface $workspaceTranslator */
        $workspaceTranslator = $this->getContainer()->get(WorkspaceTranslatorInterface::class);
        /** @var SpaceTranslatorInterface $spaceTranslator */
        $spaceTranslator = $this->getContainer()->get(SpaceTranslatorInterface::class);
        /** @var ProviderTranslatorInterface $providerTranslator */
        $providerTranslator = $this->getContainer()->get(ProviderTranslatorInterface::class);

        $this->phoenix = $phoenix;
        $this->databaseTool = $databaseTool;
        $this->entityManager = $entityManager;
        $this->userTranslator = $userTranslator;
        $this->workspaceTranslator = $workspaceTranslator;
        $this->spaceTranslator = $spaceTranslator;
        $this->providerTranslator = $providerTranslator;

        $this->loadFixtures();
    }

    public function testDeactivateUser()
    {
        $user = $this->findTestUser();
        $this->phoenix->deactivateUser($user);
        $user = $this->findTestUser();

        $this->assertFalse($user->isActive());
    }

    public function testActivateUser()
    {
        $user = $this->findTestUser();
        $this->phoenix->activateUser($user);
        $user = $this->findTestUser();

        $this->assertTrue($user->isActive());
    }

    public function testDeactivateWorkspace()
    {
        $workspace = $this->findTestWorkspace();
        $this->phoenix->deactivateWorkspace($workspace);
        $workspace = $this->findTestWorkspace();

        $this->assertFalse($workspace->isActive());
    }

    public function testActivateWorkspace()
    {
        $workspace = $this->findTestWorkspace();
        $this->phoenix->activateWorkspace($workspace);
        $workspace = $this->findTestWorkspace();

        $this->assertTrue($workspace->isActive());
    }

    public function testDeactivateSpace()
    {
        $space = $this->findTestSpace();
        $this->phoenix->deactivateSpace($space);
        $space = $this->findTestSpace();

        $this->assertFalse($space->isActive());
    }

    public function testActivateSpace()
    {
        $space = $this->findTestSpace();
        $this->phoenix->activateSpace($space);
        $space = $this->findTestSpace();

        $this->assertTrue($space->isActive());
    }

    public function testDeactivateProvider()
    {
        $provider = $this->findTestProvider();
        $this->phoenix->deactivateProvider($provider);
        $provider = $this->findTestProvider();

        $this->assertFalse($provider->isActive());
    }

    public function testActivateProvider()
    {
        $provider = $this->findTestProvider();
        $this->phoenix->activateProvider($provider);
        $provider = $this->findTestProvider();

        $this->assertTrue($provider->isActive());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset(
            $this->phoenix,
            $this->databaseTool,
            $this->entityManager,
            $this->spaceTranslator,
            $this->workspaceTranslator,
            $this->userTranslator,
            $this->providerTranslator,
        );
    }

    /**
     * @return void
     */
    private function loadFixtures(): void
    {
        $this->databaseTool->loadFixtures(
            classNames: [
                PhoenixFixtures::class,
            ],
            append: true,
        );
    }

    private function findTestUser(): User
    {
        $this->clearEntityManager();

        /** @var UserRepositoryInterface $repository */
        $repository = $this->getContainer()->get(UserRepositoryInterface::class);
        /** @var UserEntity $user */
        $userEntity = $repository->findOneBy([]);

        return $this->userTranslator->toUser($userEntity);
    }

    private function findTestWorkspace(): Workspace
    {
        $this->clearEntityManager();

        /** @var WorkspaceRepositoryInterface $repository */
        $repository = $this->getContainer()->get(WorkspaceRepositoryInterface::class);
        /** @var WorkspaceEntity $workspaceEntity */
        $workspaceEntity = $repository->findOneBy([]);

        return $this->workspaceTranslator->toWorkspace($workspaceEntity);
    }

    private function findTestSpace(): Space
    {
        $this->clearEntityManager();

        /** @var SpaceRepositoryInterface $repository */
        $repository = $this->getContainer()->get(SpaceRepositoryInterface::class);
        /** @var SpaceEntity $spaceEntity */
        $spaceEntity = $repository->findOneBy([]);

        return $this->spaceTranslator->toSpace($spaceEntity);
    }

    private function findTestProvider(): Provider
    {
        $this->clearEntityManager();

        /** @var ProviderRepositoryInterface $repository */
        $repository = $this->getContainer()->get(ProviderRepositoryInterface::class);
        /** @var ProviderEntity $providerEntity */
        $providerEntity = $repository->findOneBy([]);

        return $this->providerTranslator->toProvider($providerEntity);
    }

    private function clearEntityManager(): void
    {
        $this->entityManager->clear();
    }
}
