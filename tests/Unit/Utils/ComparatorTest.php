<?php

namespace App\Tests\Unit\Utils;

use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\Enum\Rule\Operator;
use App\Utils\Comparator;
use Generator;
use PHPUnit\Framework\TestCase;

class ComparatorTest extends TestCase
{
    private Comparator $comparator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->comparator = new Comparator();
    }

    public function testIs()
    {
        $this->assertTrue($this->comparator->is(x: 10, operator: Operator::LESS_THAN, y: 100));
        $this->assertTrue($this->comparator->is(x: 10, operator: Operator::LESS_THAN_OR_EQUAL_TO, y: 10));
        $this->assertTrue($this->comparator->is(x: 10, operator: Operator::GREATER_THAN, y: 1));
        $this->assertTrue($this->comparator->is(x: 10, operator: Operator::GREATER_THAN_OR_EQUAL_TO, y: 10));
        $this->assertTrue($this->comparator->is(x: 10, operator: Operator::EQUAL_TO, y: 10));
        $this->assertTrue($this->comparator->is(x: 10, operator: Operator::NOT_EQUAL_TO, y: 1));
        $this->assertTrue($this->comparator->is(x: 20, operator: Operator::MULTIPLE_OF, y: 5));
        $this->assertTrue($this->comparator->is(x: 60, operator: Operator::NOT_EQUAL_TO, y: 300));
        $this->assertTrue($this->comparator->is(x: 10, operator: Operator::INSET, y: [10, 20]));
        $this->assertTrue($this->comparator->is(x: 30, operator: Operator::NOT_INSET, y: [1, 2]));

        $this->assertFalse($this->comparator->is(x: 10, operator: Operator::LESS_THAN, y: 10));
        $this->assertFalse($this->comparator->is(x: 10, operator: Operator::LESS_THAN_OR_EQUAL_TO, y: 9));
        $this->assertFalse($this->comparator->is(x: 10, operator: Operator::GREATER_THAN, y: 10));
        $this->assertFalse($this->comparator->is(x: 10, operator: Operator::GREATER_THAN_OR_EQUAL_TO, y: 11));
        $this->assertFalse($this->comparator->is(x: 10, operator: Operator::EQUAL_TO, y: 100));
        $this->assertFalse($this->comparator->is(x: 10, operator: Operator::NOT_EQUAL_TO, y: 10));
        $this->assertFalse($this->comparator->is(x: 20, operator: Operator::MULTIPLE_OF, y: 200));
        $this->assertFalse($this->comparator->is(x: 60, operator: Operator::NOT_EQUAL_TO, y: 60));
        $this->assertFalse($this->comparator->is(x: 10, operator: Operator::INSET, y: [30]));
        $this->assertFalse($this->comparator->is(x: 30, operator: Operator::NOT_INSET, y: [30]));
    }

    public function testIsWithinWeekdays()
    {
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(0, 127));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(1, 127));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(2, 127));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(3, 127));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(4, 127));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(5, 127));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(6, 127));

        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(0, 1 | 0));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(1, 1 | 2));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(2, 1 | 4));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(3, 1 | 8));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(4, 1 | 16));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(5, 1 | 32));
        $this->assertTrue($this->comparator->isWithinWeekdayBoundaries(6, 1 | 64));
    }

    /** @dataProvider dataProviderForTestInTimeBoundary */
    public function testIsWithinTimeBoundary(array $params)
    {
        $timeBoundedRuleMock = $this->createMock(TimeBoundedRuleInterface::class);
        $timeBoundedRuleMock->method('getStartMinutes')->willReturn($params['ruleStartMinutes']);
        $timeBoundedRuleMock->method('getEndMinutes')->willReturn($params['ruleEndMinutes']);

        $actual = $this->comparator->isWithinTimeBoundaries($params['timeRange'], $timeBoundedRuleMock);

        $this->assertSame(
            expected: $params['expected'],
            actual: $actual,
        );
    }

    public static function dataProviderForTestInTimeBoundary(): Generator
    {
        yield 'Case #1, all day, pass' => [
            'params' => [
                'timeRange' => new TimeRange(
                    startsAt: '2050-01-01 14:00:00',
                    endsAt: '2050-01-01 15:00:00',
                ),
                'ruleStartMinutes' => 0,
                'ruleEndMinutes' => 1440,
                'expected' => true,
            ],
        ];

        yield 'Case #2, full intersection, pass' => [
            'params' => [
                'timeRange' => new TimeRange(
                    startsAt: '2050-01-01 01:00:00',
                    endsAt: '2050-01-01 02:00:00',
                ),
                'ruleStartMinutes' => 0,
                'ruleEndMinutes' => 180,
                'expected' => true,
            ],
        ];

        yield 'Case #3, intersection from beginning only, fail' => [
            'params' => [
                'timeRange' => new TimeRange(
                    startsAt: '2050-01-01 01:00:00',
                    endsAt: '2050-01-01 02:00:00',
                ),
                'ruleStartMinutes' => 0,
                'ruleEndMinutes' => 90,
                'expected' => false,
            ],
        ];

        yield 'Case #3, intersection from end only, fail' => [
            'params' => [
                'timeRange' => new TimeRange(
                    startsAt: '2050-01-01 01:00:00',
                    endsAt: '2050-01-01 02:00:00',
                ),
                'ruleStartMinutes' => 90,
                'ruleEndMinutes' => 180,
                'expected' => false,
            ],
        ];

        yield 'Case #5, no intersection (before), fail' => [
            'params' => [
                'timeRange' => new TimeRange(
                    startsAt: '2050-01-01 14:00:00',
                    endsAt: '2050-01-01 15:00:00',
                ),
                'ruleStartMinutes' => 0,
                'ruleEndMinutes' => 60,
                'expected' => false,
            ],
        ];

        yield 'Case #6, no intersection (after), fail' => [
            'params' => [
                'timeRange' => new TimeRange(
                    startsAt: '2050-01-01 14:00:00',
                    endsAt: '2050-01-01 15:00:00',
                ),
                'ruleStartMinutes' => 1000,
                'ruleEndMinutes' => 1450,
                'expected' => false,
            ],
        ];
    }
}
