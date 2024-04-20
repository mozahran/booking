<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Contract\Service\DoubleBookerBlockerInterface;
use App\DataFixtures\Tests\Service\DoubleBookerBlockerFixtures;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Exception\TimeSlotNotAvailableException;
use App\Entity\BookingEntity;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoubleBookerBlockerTest extends KernelTestCase
{
    private DoubleBookerBlockerInterface $doubleBookerBlocker;
    private AbstractDatabaseTool $databaseTool;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var DoubleBookerBlockerInterface $doubleBookerBlocker */
        $doubleBookerBlocker = $this->getContainer()->get(DoubleBookerBlockerInterface::class);
        /** @var AbstractDatabaseTool $databaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $this->doubleBookerBlocker = $doubleBookerBlocker;
        $this->databaseTool = $databaseTool;
        $this->entityManager = $entityManager;
    }

    /**
     * @dataProvider dataProviderForTestValidate
     */
    public function testValidate(
        bool $throwsException,
        array $params,
    ): void {
        if (true === $throwsException) {
            $this->expectException(TimeSlotNotAvailableException::class);
        } else {
            $this->assertTrue(true);
        }

        $this->databaseTool->loadFixtures(
            classNames: [
                DoubleBookerBlockerFixtures::class,
            ],
            append: true,
        );

        /** @var BookingEntity $bookingEntity */
        $bookingEntity = $this->entityManager->getRepository(BookingEntity::class)->findOneBy(
            [
                'startsAt' => new DateTimeImmutable(DoubleBookerBlockerFixtures::STARTS_AT),
                'endsAt' => new DateTimeImmutable(DoubleBookerBlockerFixtures::ENDS_AT),
            ],
        );

        $startsAt = $params['startsAt'];
        $endsAt = $params['endsAt'];
        $timeRange = new TimeRange(startsAt: $startsAt, endsAt: $endsAt);
        $occurrenceSet = (new OccurrenceSetBuilder())
            ->add(startsAt: $startsAt, endsAt: $endsAt)
            ->build();
        $booking = (new BookingBuilder())
            ->setSpaceId($bookingEntity->getSpace()->getId())
            ->setUserId($bookingEntity->getUser()->getId())
            ->setTimeRange($timeRange)
            ->setOccurrenceSet($occurrenceSet)
            ->build();

        $this->doubleBookerBlocker->validate($booking);
    }

    public static function dataProviderForTestValidate(): Generator
    {
        yield 'Case #1' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => '2030-01-01 15:00:00',
                'endsAt' => '2030-01-01 18:00:00',
            ],
        ];

        yield 'Case #2' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => DoubleBookerBlockerFixtures::STARTS_AT,
                'endsAt' => DoubleBookerBlockerFixtures::ENDS_AT,
            ],
        ];

        yield 'Case #3' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => '2010-01-01 15:00:00',
                'endsAt' => '2010-01-01 18:00:00',
            ],
        ];
    }
}
