<?php

namespace App\Tests\Functional\Service\Booking\Gatekeeper;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Contract\Repository\BookingRepositoryInterface;
use App\Contract\Service\GatekeeperInterface;
use App\Contract\Service\RuleSmithInterface;
use App\Domain\DataObject\Booking\Booking;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\InvalidTimeRangeException;
use App\Domain\Exception\RuleViolationException;
use DateTime;
use DateTimeInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BufferRuleValidatorTest extends KernelTestCase
{
    private RuleSmithInterface $ruleSmith;
    private GatekeeperInterface $gatekeeper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ruleSmith = $this->getContainer()->get(RuleSmithInterface::class);
        $this->gatekeeper = $this->getContainer()->get(GatekeeperInterface::class);
    }

    public function testThrowException()
    {
        $this->expectException(RuleViolationException::class);

        $existingBookingTimeRange = $this->getBookingRepository()->findOne(id: 1)->getTimeRange();

        $startsAt = DateTime::createFromImmutable(clone $existingBookingTimeRange->getEndsAt())->modify('+5 minutes');
        $endsAt = (clone $startsAt)->modify('+1 hour')->format(DateTimeInterface::ATOM);
        $startsAt = $startsAt->format(DateTimeInterface::ATOM);

        $booking = $this->createBooking(
            startsAt: $startsAt,
            endsAt: $endsAt,
            spaceId: 1,
        );

        $rule = $this->ruleSmith->parse(
            type: RuleType::BUFFER,
            rule: '{"value": 60,"spaceIds":null}',
        );

        $this->gatekeeper->validate(
            rules: [$rule],
            booking: $booking,
        );
    }

    public function testRuleNotApplicable()
    {
        $this->expectNotToPerformAssertions();

        $existingBookingTimeRange = $this->getBookingRepository()->findOne(id: 1)->getTimeRange();

        $startsAt = DateTime::createFromImmutable(clone $existingBookingTimeRange->getEndsAt())->modify('+5 minutes');
        $endsAt = (clone $startsAt)->modify('+1 hour')->format(DateTimeInterface::ATOM);
        $startsAt = $startsAt->format(DateTimeInterface::ATOM);

        $booking = $this->createBooking(
            startsAt: $startsAt,
            endsAt: $endsAt,
            spaceId: 1,
        );

        $rule = $this->ruleSmith->parse(
            type: RuleType::BUFFER,
            rule: '{"value": 60,"spaceIds":[2]}',
        );

        $this->gatekeeper->validate(
            rules: [$rule],
            booking: $booking,
        );

    }

    public function testNoExceptionThrown()
    {
        $existingBookingTimeRange = $this->getBookingRepository()->findOne(id: 1)->getTimeRange();

        $startsAt = DateTime::createFromImmutable(clone $existingBookingTimeRange->getEndsAt())->modify('+2 hours');
        $endsAt = (clone $startsAt)->modify('+1 hours')->format(DateTimeInterface::ATOM);
        $startsAt = $startsAt->format(DateTimeInterface::ATOM);

        $booking = $this->createBooking(
            startsAt: $startsAt,
            endsAt: $endsAt,
            spaceId: 1,
        );

        $rule = $this->ruleSmith->parse(
            type: RuleType::BUFFER,
            rule: '{"value": 60,"spaceIds":null}',
        );

        $this->gatekeeper->validate(
            rules: [$rule],
            booking: $booking,
        );

        $this->expectNotToPerformAssertions();
    }

    private function getBookingRepository(): BookingRepositoryInterface
    {
        /** @var BookingRepositoryInterface $repository */
        $repository = $this->getContainer()->get(BookingRepositoryInterface::class);

        return $repository;
    }

    /**
     * @throws InvalidTimeRangeException
     */
    private function createBooking(
        string $startsAt,
        string $endsAt,
        int $spaceId,
    ): Booking {
        $timeRange = new TimeRange(
            startsAt: $startsAt,
            endsAt: $endsAt,
        );

        $occurrenceSet = (new OccurrenceSetBuilder())
            ->setTimeRange($timeRange)
            ->add(
                startsAt: $startsAt,
                endsAt: $endsAt,
            )
            ->build();

        return (new BookingBuilder())
            ->setOccurrenceSet($occurrenceSet)
            ->setTimeRange($timeRange)
            ->setSpaceId($spaceId)
            ->build();
    }
}
