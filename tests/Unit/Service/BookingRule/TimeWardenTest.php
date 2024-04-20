<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\BookingRule;

use App\Contract\DataObject\TimeBoundedRuleInterface;
use App\Service\BookingRule\TimeWarden;
use App\Tests\Unit\Utils\TestBookingFactory;
use DateTimeImmutable;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TimeWardenTest extends KernelTestCase
{
    /**
     * @dataProvider dataProviderForTestValidateBoundaries
     */
    public function testValidateBoundaries(
        bool $success,
        array $params,
    ): void {
        $timeWarden = new TimeWarden();
        $booking = TestBookingFactory::createSingleOccurrenceBooking(
            startsAt: new DateTimeImmutable($params['startsAt']),
            endsAt: new DateTimeImmutable($params['endsAt']),
        );

        $rule = $this->createMock(TimeBoundedRuleInterface::class);
        $rule->method('getDaysBitmask')->willReturn($params['dayBitmask']);
        $rule->method('getStartMinutes')->willReturn($params['ruleStart']);
        $rule->method('getEndMinutes')->willReturn($params['ruleEnd']);

        $ruleViolationList = $timeWarden->validateBoundaries(
            booking: $booking,
            rule: $rule,
        );

        if (true === $success) {
            $this->assertTrue($ruleViolationList->isEmpty());
        } else {
            $this->assertFalse($ruleViolationList->isEmpty());
        }
    }

    public static function dataProviderForTestValidateBoundaries(): Generator
    {
        yield 'Case #1, all boundaries respected' => [
            'success' => true,
            'params' => [
                'startsAt' => '2024-01-01 01:00:00',
                'endsAt' => '2024-01-01 02:00:00',
                'dayBitmask' => 127,
                'ruleStart' => 0,
                'ruleEnd' => 1440,
            ],
        ];

        yield 'Case #3, ends at midnight' => [
            'success' => true,
            'params' => [
                'startsAt' => '2024-01-01 23:00:00',
                'endsAt' => '2024-01-02 00:00:00',
                'dayBitmask' => 127,
                'ruleStart' => 1380,
                'ruleEnd' => 1440,
            ],
        ];

        yield 'Case #4, ends after midnight' => [
            'success' => false,
            'params' => [
                'startsAt' => '2024-01-01 23:00:00',
                'endsAt' => '2024-01-02 00:00:01',
                'dayBitmask' => 127,
                'ruleStart' => 1380,
                'ruleEnd' => 1440,
            ],
        ];

        yield 'Case #5, outside allowed time range' => [
            'success' => false,
            'params' => [
                'startsAt' => '2024-01-01 01:00:00',
                'endsAt' => '2024-01-01 01:30:00',
                'dayBitmask' => 127,
                'ruleStart' => 360,
                'ruleEnd' => 1440,
            ],
        ];

        yield 'Case #6, day mismatch' => [
            'success' => false,
            'params' => [
                'startsAt' => '2024-01-01 01:00:00',
                'endsAt' => '2024-01-01 01:30:00',
                'dayBitmask' => 0,
                'ruleStart' => 0,
                'ruleEnd' => 1440,
            ],
        ];
    }
}
