<?php

namespace App\DataFixtures;

use App\Entity\SpaceEntity;
use App\Entity\WorkspaceEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SpaceFixtures extends Fixture implements DependentFixtureInterface
{
    public const REF_01 = 'ref.space.1';
    public const REF_02 = 'ref.space.2';

    public function load(ObjectManager $manager): void
    {
        /** @var WorkspaceEntity $workspace1 */
        $workspace1 = $this->getReference(WorkspaceFixtures::REF_01);
        /** @var WorkspaceEntity $workspace2 */
        $workspace2 = $this->getReference(WorkspaceFixtures::REF_02);

        $space1 = new SpaceEntity();
        $space1->setName('Space #1');
        $space1->setWorkspace($workspace1);

        $space2 = new SpaceEntity();
        $space2->setName('Space #2');
        $space2->setWorkspace($workspace2);

        $manager->persist($space1);
        $manager->persist($space2);

        $manager->flush();

        $this->setReference(self::REF_01, $space1);
        $this->setReference(self::REF_02, $space2);
    }

    public function getDependencies(): array
    {
        return [
            WorkspaceFixtures::class,
        ];
    }
}
