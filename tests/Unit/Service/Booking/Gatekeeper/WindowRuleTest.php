<?php

namespace App\Tests\Unit\Service\Booking\Gatekeeper;

use App\Builder\BookingBuilder;
use App\Builder\OccurrenceSetBuilder;
use App\Contract\Service\GatekeeperInterface;
use App\Contract\Service\RuleSmithInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleViolationException;
use DateTime;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WindowRuleTest extends KernelTestCase
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
        $userId = $params['userId'];
        $rules = $params['rules'];

        if (true === $throwsException) {
            $this->expectException(RuleViolationException::class);
        } else {
            $this->assertTrue(true);
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
            ->setUserId($userId)
            ->build();

        $this->gatekeeper->validate(
            rules: $rules,
            booking: $booking,
        );
    }

    public static function dataProviderForTestRule(): Generator
    {
        /** @var RuleSmithInterface $ruleSmith */
        $ruleSmith = self::getContainer()->get(RuleSmithInterface::class);

        $today = new DateTime();

        yield 'CASE #01, applicable, predicate 1, violating' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => (clone $today)->modify('+5 hours')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+6 hours')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":1,"value":1,"roles":null,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #02, applicable, predicate 1, no exception' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => (clone $today)->modify('+50 hours')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+51 hours')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":1,"value":1,"roles":null,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #03, applicable, predicate 2, no exception' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => (clone $today)->modify('+120 minutes')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+180 minutes')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":2,"value":1,"roles":null,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #04, applicable, predicate 2, exception' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => (clone $today)->modify('+10 minutes')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+40 minutes')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":2,"value":1,"roles":null,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #05, applicable, predicate 3, no exception' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => (clone $today)->modify('+10 minutes')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+40 minutes')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":3,"value":1,"roles":null,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #06, applicable, predicate 3, exception' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => (clone $today)->modify('+3000 minutes')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+3060 minutes')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":3,"value":1,"roles":null,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #07, not applicable, no space match' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => (clone $today)->modify('+10 minutes')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+20 minutes')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":1,"value":1,"roles":null,"spaceIds":[2]}',
                    ),
                ],
            ],
        ];

        yield 'CASE #08, not applicable, no user role match' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => (clone $today)->modify('+61 minutes')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+90 minutes')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":2,"value":1,"roles":["ROLE_TEST"],"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #08, applicable, user role match, exception' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => (clone $today)->modify('+10 minutes')->format(TimeRange::SHORT_FORMAT),
                'endsAt' => (clone $today)->modify('+20 minutes')->format(TimeRange::SHORT_FORMAT),
                'spaceId' => 1,
                'userId' => 1,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::WINDOW,
                        rule: '{"predicate":2,"value":1,"roles":["ROLE_ADMIN"],"spaceIds":null}',
                    ),
                ],
            ],
        ];
    }
}
