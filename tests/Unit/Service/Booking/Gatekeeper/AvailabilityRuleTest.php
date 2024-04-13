<?php

namespace App\Tests\Unit\Service\Booking\Gatekeeper;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Contract\Service\GatekeeperInterface;
use App\Contract\Service\RuleSmithInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleViolationException;
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
     * @dataProvider dataProviderForTestRule
     */
    public function testRule(
        bool $throwsException,
        array $params,
    ) {
        $startsAt = $params['startsAt'];
        $endsAt = $params['endsAt'];
        $spaceId = $params['spaceId'];
        $rules = $params['rules'];

        if (true === $throwsException) {
            $this->expectException(RuleViolationException::class);
        }

        $booking = $this->createSingleOccurrenceBooking(
            startsAt: $startsAt,
            endsAt: $endsAt,
            spaceId: $spaceId,
        );

        $this->gatekeeper->validate(
            rules: $rules,
            booking: $booking,
        );

        if (false === $throwsException) {
            $this->assertTrue(true);
        }
    }

    public static function dataProviderForTestRule(): Generator
    {
        /** @var RuleSmithInterface $ruleSmith */
        $ruleSmith = self::getContainer()->get(RuleSmithInterface::class);

        yield 'CASE #01, applicable, outside allowed time range' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => '2050-01-01 10:00',
                'endsAt' => '2050-01-01 11:00',
                'spaceId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::AVAILABILITY,
                        rule: '{"daysBitmask":127,"start":60,"end":120,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #02, applicable, within allowed time range' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => '2050-01-01 20:00',
                'endsAt' => '2050-01-01 21:00',
                'spaceId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::AVAILABILITY,
                        rule: '{"daysBitmask":127,"start":0,"end":1440,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #03, applicable, outside allowed weekdays' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => '2050-01-01 20:00',
                'endsAt' => '2050-01-01 21:00',
                'spaceId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::AVAILABILITY,
                        rule: '{"daysBitmask":32,"start":0,"end":1440,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #04, applicable, within allowed weekdays' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => '2050-01-01 20:00',
                'endsAt' => '2050-01-01 21:00',
                'spaceId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::AVAILABILITY,
                        rule: '{"daysBitmask":64,"start":0,"end":1440,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #05, no space match, not applicable' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => '2050-01-01 20:00',
                'endsAt' => '2050-01-01 21:00',
                'spaceId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::AVAILABILITY,
                        rule: '{"daysBitmask":0,"start":0,"end":0,"spaceIds":[2,3]}',
                    ),
                ],
            ],
        ];
    }
}
