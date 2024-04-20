<?php

declare(strict_types=1);

namespace App\DataFixtures\Tests\Service;

use App\DataFixtures\SpaceFixtures;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\WorkspaceFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PhoenixFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            WorkspaceFixtures::class,
            SpaceFixtures::class,
        ];
    }
}
