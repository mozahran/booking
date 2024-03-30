<?php

namespace App\DataFixtures;

use App\Entity\ProviderEntity;
use App\Entity\WorkspaceEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WorkspaceFixtures extends Fixture implements DependentFixtureInterface
{
    public const REF_01 = 'ref.workspace.1';
    public const REF_02 = 'ref.workspace.2';

    public function load(ObjectManager $manager): void
    {
        /** @var ProviderEntity $provider1 */
        $provider1 = $this->getReference(ProviderFixtures::REF_01);

        $workspace1 = new WorkspaceEntity();
        $workspace1->setName('Workspace #1');
        $workspace1->setProvider($provider1);

        $workspace2 = new WorkspaceEntity();
        $workspace2->setName('Workspace #2');
        $workspace2->setProvider($provider1);

        $manager->persist($workspace1);
        $manager->persist($workspace2);

        $manager->flush();

        $this->setReference(self::REF_01, $workspace1);
        $this->setReference(self::REF_02, $workspace2);
    }

    public function getDependencies(): array
    {
        return [
            ProviderFixtures::class,
        ];
    }
}
