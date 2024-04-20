<?php

namespace App\DataFixtures;

use App\Domain\Enum\RuleType;
use App\Entity\BookingRuleEntity;
use App\Entity\WorkspaceEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BookingRuleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const REF_01 = 'app.booking-rules.01';

    public function load(ObjectManager $manager): void
    {
        /** @var WorkspaceEntity $workspace1 */
        $workspace1 = $this->getReference(WorkspaceFixtures::REF_01, WorkspaceEntity::class);

        $rule1 = new BookingRuleEntity();
        $rule1->setName('random-name');
        $rule1->setActive(true);
        $rule1->setContent('{"daysBitmask":127,"start":60,"end":120,"spaceIds":null}');
        $rule1->setType(RuleType::AVAILABILITY);
        $rule1->setWorkspace($workspace1);

        $manager->persist($rule1);
        $manager->flush();

        $this->setReference(self::REF_01, $rule1);
    }

    public function getDependencies(): array
    {
        return [
            WorkspaceFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return [
            'app',
        ];
    }
}
