<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\DataObject\Booking\TimeRange;
use App\Entity\BookingEntity;
use App\Entity\OccurrenceEntity;
use App\Entity\SpaceEntity;
use App\Entity\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BookingFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const REF_01 = 'ref.booking.1';

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();
        $startsAt = new \DateTimeImmutable(sprintf('%s 21:00:00', $now->format(TimeRange::DATE_FORMAT)));
        $endsAt = new \DateTimeImmutable(sprintf('%s 22:00:00', $now->format(TimeRange::DATE_FORMAT)));

        $booking1 = new BookingEntity();
        $booking1->setUser(user: $this->getUserEntity());
        $booking1->setSpace(space: $this->getSpaceEntity());
        $booking1->setStartsAt(startsAt: $startsAt);
        $booking1->setEndsAt(endsAt: $endsAt);

        $occurrence1 = new OccurrenceEntity();
        $occurrence1->setStartsAt(startsAt: $booking1->getStartsAt());
        $occurrence1->setEndsAt(endsAt: $booking1->getEndsAt());

        $booking1->addOccurrence(occurrence: $occurrence1);

        $manager->persist($booking1);
        $manager->flush();

        $this->setReference(self::REF_01, $booking1);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            SpaceFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return [
            'app',
        ];
    }

    private function getUserEntity(): UserEntity
    {
        /** @var UserEntity $userEntity */
        $userEntity = $this->getReference(
            UserFixtures::REF_01,
            UserEntity::class,
        );

        return $userEntity;
    }

    private function getSpaceEntity(): SpaceEntity
    {
        /** @var SpaceEntity $spaceEntity */
        $spaceEntity = $this->getReference(
            SpaceFixtures::REF_01,
            SpaceEntity::class,
        );

        return $spaceEntity;
    }
}
