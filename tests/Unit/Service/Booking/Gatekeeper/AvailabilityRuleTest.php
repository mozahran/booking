<?php

namespace App\Tests\Unit\Service\Booking\Gatekeeper;

use App\Contract\Service\GatekeeperInterface;
use App\Contract\Service\RuleSmithInterface;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleViolationException;
use App\Tests\Utils\TestBookingFactory;
use App\Utils\DateSmith;
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
        if (true === $throwsException) {
            $this->expectException(RuleViolationException::class);
        } else {
            $this->assertTrue(true);
        }

        $booking = TestBookingFactory::createSingleOccurrenceBooking(
            startsAt: $params['startsAt'],
            endsAt: $params['endsAt'],
            spaceId: $params['spaceId'],
            userId: $params['userId'],
        );

        $this->gatekeeper->validate(
            rules: $params['rules'],
            booking: $booking,
        );
    }

    public static function dataProviderForTestRule(): Generator
    {
        /** @var RuleSmithInterface $ruleSmith */
        $ruleSmith = self::getContainer()->get(RuleSmithInterface::class);

        yield 'CASE #01, applicable, outside allowed time range' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => DateSmith::withTime(10, 0),
                'endsAt' => DateSmith::withTime(11, 0),
                'spaceId' => 1,
                'userId' => 1,
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
                'startsAt' => DateSmith::withTime(20, 0),
                'endsAt' => DateSmith::withTime(21, 0),
                'spaceId' => 1,
                'userId' => 1,
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
                'startsAt' => DateSmith::withTime(20, 0),
                'endsAt' => DateSmith::withTime(21, 0),
                'spaceId' => 1,
                'userId' => 1,
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
                'startsAt' => DateSmith::withTime(20, 0),
                'endsAt' => DateSmith::withTime(21, 0),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::AVAILABILITY,
                        rule: '{"daysBitmask":127,"start":0,"end":1440,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #05, no space match, not applicable' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => DateSmith::withTime(20, 0),
                'endsAt' => DateSmith::withTime(21, 0),
                'spaceId' => 1,
                'userId' => 1,
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
