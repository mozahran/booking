<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\BookingRule\Gatekeeper;

use App\Contract\Repository\OccurrenceRepositoryInterface;
use App\Contract\Service\BookingRule\TimeWardenInterface;
use App\Contract\Utils\TimeRangeSmithInterface;
use App\Domain\DataObject\Rule\Quota;
use App\Domain\Enum\Rule\AggregationMetric;
use App\Domain\Enum\Rule\Mode;
use App\Domain\Enum\Rule\Period;
use App\Domain\Exception\RuleViolationException;
use App\Service\BookingRule\Gatekeeper;
use App\Tests\Unit\Utils\TestBookingFactory;
use App\Utils\DateSmith;
use App\Utils\RuleViolationList;
use App\Validator\Rule\QuotaRuleValidator;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QuotaRuleValidatorTest extends KernelTestCase
{
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
            $this->expectNotToPerformAssertions();
        }

        $booking = TestBookingFactory::createSingleOccurrenceBooking(
            startsAt: $params['startsAt'],
            endsAt: $params['endsAt'],
            spaceId: $params['spaceId'],
            userId: $params['userId'],
        );

        $this->getGatekeeper(params: $params)->validate(
            rules: $params['rules'],
            booking: $booking,
        );
    }

    public static function dataProviderForTestRule(): Generator
    {
        yield 'CASE #01, per day, throws exception' => [
            'throwsException' => true,
            'params' => [
                'spaceId' => 1,
                'userId' => 1,
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'repositoryMethodName' => 'getTimeUsageByUserAndSpaceInGivenTimeRanges',
                'repositoryMethodReturnValue' => 60,
                'rules' => [
                    new Quota(
                        daysBitmask: 127,
                        startMinutes: 0,
                        endMinutes: 1440,
                        value: 60,
                        aggregationMetric: AggregationMetric::TIME_USAGE_MAXIMUM,
                        mode: Mode::ALL_USERS,
                        period: Period::PER_DAY,
                        roles: null,
                        spaceIds: null,
                    ),
                ],
            ],
        ];

        yield 'CASE #02, per day, no exception' => [
            'throwsException' => false,
            'params' => [
                'spaceId' => 1,
                'userId' => 1,
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'repositoryMethodName' => 'getTimeUsageByUserAndSpaceInGivenTimeRanges',
                'repositoryMethodReturnValue' => 60,
                'rules' => [
                    new Quota(
                        daysBitmask: 127,
                        startMinutes: 0,
                        endMinutes: 1440,
                        value: 1000,
                        aggregationMetric: AggregationMetric::TIME_USAGE_MAXIMUM,
                        mode: Mode::ALL_USERS,
                        period: Period::PER_DAY,
                        roles: null,
                        spaceIds: null,
                    ),
                ],
            ],
        ];

        yield 'CASE #03, per day, throws exception' => [
            'throwsException' => true,
            'params' => [
                'spaceId' => 1,
                'userId' => 1,
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'repositoryMethodName' => 'countByUserAndSpaceInGivenTimeRanges',
                'repositoryMethodReturnValue' => 1,
                'rules' => [
                    new Quota(
                        daysBitmask: 127,
                        startMinutes: 0,
                        endMinutes: 1440,
                        value: 1,
                        aggregationMetric: AggregationMetric::BOOKING_COUNT_MAXIMUM,
                        mode: Mode::ALL_USERS,
                        period: Period::PER_DAY,
                        roles: null,
                        spaceIds: null,
                    ),
                ],
            ],
        ];

        yield 'CASE #04, per day, no exception' => [
            'throwsException' => false,
            'params' => [
                'spaceId' => 1,
                'userId' => 1,
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'repositoryMethodName' => 'countByUserAndSpaceInGivenTimeRanges',
                'repositoryMethodReturnValue' => 1,
                'rules' => [
                    new Quota(
                        daysBitmask: 127,
                        startMinutes: 0,
                        endMinutes: 1440,
                        value: 2,
                        aggregationMetric: AggregationMetric::BOOKING_COUNT_MAXIMUM,
                        mode: Mode::ALL_USERS,
                        period: Period::PER_DAY,
                        roles: null,
                        spaceIds: null,
                    ),
                ],
            ],
        ];

        yield 'CASE #05, per week, throws exception' => [
            'throwsException' => true,
            'params' => [
                'spaceId' => 1,
                'userId' => 1,
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'repositoryMethodName' => 'countByUserAndSpaceInGivenTimeRanges',
                'repositoryMethodReturnValue' => 600,
                'rules' => [
                    new Quota(
                        daysBitmask: 127,
                        startMinutes: 0,
                        endMinutes: 1440,
                        value: 60,
                        aggregationMetric: AggregationMetric::TIME_USAGE_MAXIMUM,
                        mode: Mode::ALL_USERS,
                        period: Period::PER_WEEK,
                        roles: null,
                        spaceIds: null,
                    ),
                ],
            ],
        ];

        yield 'CASE #6, per week, no exception' => [
            'throwsException' => false,
            'params' => [
                'spaceId' => 1,
                'userId' => 1,
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'repositoryMethodName' => 'countByUserAndSpaceInGivenTimeRanges',
                'repositoryMethodReturnValue' => 60,
                'rules' => [
                    new Quota(
                        daysBitmask: 127,
                        startMinutes: 0,
                        endMinutes: 1440,
                        value: 600,
                        aggregationMetric: AggregationMetric::TIME_USAGE_MAXIMUM,
                        mode: Mode::ALL_USERS,
                        period: Period::PER_WEEK,
                        roles: null,
                        spaceIds: null,
                    ),
                ],
            ],
        ];
    }

    private function getGatekeeper(
        array $params,
    ): Gatekeeper {
        $timeWarden = $this->createMock(TimeWardenInterface::class);
        $timeWarden->method('validateBoundaries')->willReturn(RuleViolationList::empty());

        $methodName = $params['repositoryMethodName'];
        $methodReturnValue = $params['repositoryMethodReturnValue'];

        $occurrenceRepositoryMock = $this->createMock(OccurrenceRepositoryInterface::class);
        $occurrenceRepositoryMock->method($methodName)->willReturn($methodReturnValue);

        $occurrenceRepositoryMock = $this->getContainer()->get(OccurrenceRepositoryInterface::class);

        return new Gatekeeper(
            ruleValidators: [
                new QuotaRuleValidator(
                    occurrenceRepository: $occurrenceRepositoryMock,
                    timeRangeSmith: $this->getContainer()->get(TimeRangeSmithInterface::class),
                ),
            ],
        );
    }
}
