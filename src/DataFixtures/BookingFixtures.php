<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\BookingEntity;
use App\Entity\OccurrenceEntity;
use App\Entity\SpaceEntity;
use App\Entity\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BookingFixtures extends Fixture implements DependentFixtureInterface
{
    public const REF_01 = 'ref.booking.1';

    public function load(ObjectManager $manager): void
    {
        /** @var UserEntity $user1 */
        $user1 = $this->getReference(UserFixtures::REF_01);
        /** @var SpaceEntity $space1 */
        $space1 = $this->getReference(SpaceFixtures::REF_01);

        $booking1 = new BookingEntity();
        $booking1->setUser($user1);
        $booking1->setSpace($space1);
        $booking1->setStartsAt(new \DateTimeImmutable('2024-01-01 21:00:00'));
        $booking1->setEndsAt(new \DateTimeImmutable('2024-01-01 22:00:00'));

        $occurrence1 = new OccurrenceEntity();
        $occurrence1->setStartsAt($booking1->getStartsAt());
        $occurrence1->setEndsAt($booking1->getEndsAt());

        $booking1->addOccurrence($occurrence1);

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
}
