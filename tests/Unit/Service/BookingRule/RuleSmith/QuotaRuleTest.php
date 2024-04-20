<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\BookingRule\RuleSmith;

use App\Contract\Service\BookingRule\RuleSmithInterface;
use App\Domain\DataObject\Rule\Availability;
use App\Domain\DataObject\Rule\Quota;
use App\Domain\Enum\Rule\AggregationMetric;
use App\Domain\Enum\Rule\Mode;
use App\Domain\Enum\Rule\Period;
use App\Domain\Enum\RuleType;
use App\Domain\Enum\UserRole;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QuotaRuleTest extends KernelTestCase
{
    private RuleSmithInterface $ruleSmith;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var RuleSmithInterface $ruleSmith */
        $ruleSmith = self::getContainer()->get(RuleSmithInterface::class);
        $this->ruleSmith = $ruleSmith;
    }

    /**
     * @dataProvider dataProviderForTestParseRule
     */
    public function testParseRule(
        array $params,
    ): void {
        $ruleType = RuleType::QUOTA;
        $normalized = [
            'daysBitmask' => $params['daysBitmask'],
            'start' => $params['start'],
            'end' => $params['end'],
            'value' => $params['value'],
            'aggregationMetric' => $params['aggregationMetric'],
            'mode' => $params['mode'],
            'period' => $params['period'],
            'roles' => $params['roles'],
            'spaceIds' => $params['spaceIds'],
        ];

        /** @var Quota $rule */
        $rule = $this->ruleSmith->parse(
            type: $ruleType,
            rule: json_encode($normalized),
        );

        $this->assertSame($rule->getType(), $ruleType);
        $this->assertSame($rule->getDaysBitmask(), $params['daysBitmask']);
        $this->assertSame($rule->getStartMinutes(), $params['start']);
        $this->assertSame($rule->getEndMinutes(), $params['end']);
        $this->assertSame($rule->getAggregationMetric()->value, $params['aggregationMetric']);
        $this->assertSame($rule->getMode()->value, $params['mode']);
        $this->assertSame($rule->getPeriod()->value, $params['period']);
        $this->assertSame($rule->getSpaceIds(), $params['spaceIds']);
        $this->assertSame($rule->getRoles(), $params['roles']);
        $this->assertSame($rule->normalize(), $normalized);
    }

    public static function dataProviderForTestParseRule(): Generator
    {
        yield 'Case #1' => [
            'params' => [
                'daysBitmask' => 127,
                'start' => 0,
                'end' => 1440,
                'value' => 60,
                'aggregationMetric' => AggregationMetric::TIME_USAGE_MAXIMUM->value,
                'mode' => Mode::ALL_USERS->value,
                'period' => Period::PER_DAY->value,
                'roles' => null,
                'spaceIds' => null,
            ],
        ];

        yield 'Case #2' => [
            'params' => [
                'daysBitmask' => 127,
                'start' => 0,
                'end' => 1440,
                'value' => 60,
                'aggregationMetric' => AggregationMetric::BOOKING_COUNT_MAXIMUM->value,
                'mode' => Mode::USERS_WITH_ANY_OF_TAGS->value,
                'period' => Period::PER_WEEK->value,
                'roles' => [
                    UserRole::OWNER->value,
                    UserRole::ADMIN->value,
                ],
                'spaceIds' => [1, 2],
            ],
        ];
    }
}
