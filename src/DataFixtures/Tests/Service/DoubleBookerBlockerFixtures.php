<?php

declare(strict_types=1);

namespace App\DataFixtures\Tests\Service;

use App\DataFixtures\SpaceFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\BookingEntity;
use App\Entity\OccurrenceEntity;
use App\Entity\SpaceEntity;
use App\Entity\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DoubleBookerBlockerFixtures extends Fixture implements DependentFixtureInterface
{
    public const STARTS_AT = '2050-01-01 15:00:00';
    public const ENDS_AT = '2050-01-01 17:00:00';

    public function load(ObjectManager $manager): void
    {
        $user1 = $this->getReference(name: UserFixtures::REF_01, class: UserEntity::class);
        $space1 = $this->getReference(name: SpaceFixtures::REF_01, class: SpaceEntity::class);

        $booking1 = new BookingEntity();
        $booking1->setUser(user: $user1);
        $booking1->setSpace(space: $space1);
        $booking1->setStartsAt(startsAt: new \DateTimeImmutable(self::STARTS_AT));
        $booking1->setEndsAt(endsAt: new \DateTimeImmutable(self::ENDS_AT));

        $occurrence1 = new OccurrenceEntity();
        $occurrence1->setStartsAt(startsAt: $booking1->getStartsAt());
        $occurrence1->setEndsAt(endsAt: $booking1->getEndsAt());

        $booking1->addOccurrence(occurrence: $occurrence1);

        $manager->persist($booking1);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            SpaceFixtures::class,
        ];
    }
}
