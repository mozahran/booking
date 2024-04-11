<?php

namespace App\Tests\Unit\Service\Booking\Gatekeeper;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Contract\Service\GatekeeperInterface;
use App\Contract\Service\RuleSmithInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleViolationException;
use App\Service\RuleSmith;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AvailabilityRuleTest extends KernelTestCase
{
    private GatekeeperInterface $gatekeeper;

    protected function setUp(): void
    {
        $this->gatekeeper = $this->getContainer()->get(GatekeeperInterface::class);
    }

    /**
     * @dataProvider dataProviderForTestAvailability
     */
    public function testAvailability(
        string $startsAt,
        string $endsAt,
        int $spaceId,
        array $rules,
        bool $throwsException,
    ) {
        if (true === $throwsException) {
            $this->expectException(RuleViolationException::class);
        }

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

        $booking = (new BookingBuilder())
            ->setOccurrenceSet($occurrenceSet)
            ->setTimeRange($timeRange)
            ->setSpaceId($spaceId)
            ->build();

        $this->gatekeeper->validate(
            rules: $rules,
            booking: $booking,
        );

        if (false === $throwsException) {
            $this->assertTrue(true);
        }
    }

    public static function dataProviderForTestAvailability(): Generator
    {
        /** @var RuleSmithInterface $ruleSmith */
        $ruleSmith = self::getContainer()->get(RuleSmithInterface::class);

        yield 'CASE #01, applicable, outside allowed time range' => [
            'startsAt' => '2050-01-01 10:00',
            'endsAt' => '2050-01-01 11:00',
            'spaceId' => 1,
            'rules' => [
                $ruleSmith->parse(
                    type: RuleType::AVAILABILITY,
                    rule: '{"daysBitmask":127,"start":60,"end":120,"spaceIds":null}',
                ),
            ],
            'throwsException' => true,
        ];

        yield 'CASE #02, applicable, within allowed time range' => [
            'startsAt' => '2050-01-01 20:00',
            'endsAt' => '2050-01-01 21:00',
            'spaceId' => 1,
            'rules' => [
                $ruleSmith->parse(
                    type: RuleType::AVAILABILITY,
                    rule: '{"daysBitmask":127,"start":0,"end":1440,"spaceIds":null}',
                ),
            ],
            'throwsException' => false,
        ];

        yield 'CASE #03, applicable, outside allowed weekdays' => [
            'startsAt' => '2050-01-01 20:00',
            'endsAt' => '2050-01-01 21:00',
            'spaceId' => 1,
            'rules' => [
                $ruleSmith->parse(
                    type: RuleType::AVAILABILITY,
                    rule: '{"daysBitmask":32,"start":0,"end":1440,"spaceIds":null}',
                ),
            ],
            'throwsException' => true,
        ];

        yield 'CASE #04, applicable, within allowed weekdays' => [
            'startsAt' => '2050-01-01 20:00',
            'endsAt' => '2050-01-01 21:00',
            'spaceId' => 1,
            'rules' => [
                $ruleSmith->parse(
                    type: RuleType::AVAILABILITY,
                    rule: '{"daysBitmask":64,"start":0,"end":1440,"spaceIds":null}',
                ),
            ],
            'throwsException' => false,
        ];

        yield 'CASE #05, not applicable, violating all rules' => [
            'startsAt' => '2050-01-01 20:00',
            'endsAt' => '2050-01-01 21:00',
            'spaceId' => 1,
            'rules' => [
                $ruleSmith->parse(
                    type: RuleType::AVAILABILITY,
                    rule: '{"daysBitmask":0,"start":0,"end":0,"spaceIds":[2,3]}',
                ),
            ],
            'throwsException' => false,
        ];
    }
}
