<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Booking\Gatekeeper;

use App\Contract\Resolver\UserResolverInterface;
use App\Contract\Service\TimeWardenInterface;
use App\Domain\DataObject\Rule\Condition;
use App\Domain\DataObject\Rule\ConditionGroup;
use App\Domain\DataObject\Rule\Deny;
use App\Domain\DataObject\User;
use App\Domain\Enum\Rule\Operand;
use App\Domain\Enum\Rule\Operator;
use App\Domain\Exception\RuleViolationException;
use App\Service\Gatekeeper;
use App\Tests\Utils\TestBookingFactory;
use App\Utils\DateSmith;
use App\Utils\RuleViolationList;
use App\Validator\Rule\DenyRuleValidator;
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
        yield 'CASE #01, duration, throws exception' => [
            'throwsException' => true,
            'params' => [
                'spaceId' => 1,
                'userId' => 1,
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'rules' => [
                    new Deny(
                        daysBitmask: 127,
                        startMinutes: 0,
                        endMinutes: 1440,
                        conditionGroups: [
                            new ConditionGroup(
                                [
                                    new Condition(Operand::ITS_DURATION, Operator::IS_MULTIPLE_OF, 6),
                                ],
                            ),
                        ],
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
        $timeWarden->method('validateBoundaries')->willReturn(RuleViolationList::create());

        $userResolverMock = $this->createMock(UserResolverInterface::class);
        $userResolverMock->method('resolve')->willReturn(
            new User(
                name: 'John Doe',
                email: 'john.doe@example.com',
                active: true,
                roles: $params['userRoles'] ?? [],
                id: $params['userId'],
            ),
        );

        return new Gatekeeper(
            ruleValidators: [
                new DenyRuleValidator(
                    userResolver: $userResolverMock,
                    timeWarden: $timeWarden,
                ),
            ],
        );
    }
}
