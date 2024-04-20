<?php

namespace App\Tests\Unit\Builder;

use App\Builder\OccurrenceSetBuilder;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\RecurrenceRule;
use App\Domain\DataObject\Booking\Status;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Set\OccurrenceSet;
use Generator;
use PHPUnit\Framework\TestCase;

class OccurrenceSetBuilderTest extends TestCase
{
    public const DATETIME_FORMAT = 'Y-m-d H:i:00';

    /**
     * @dataProvider dataProviderForTestBuild
     */
    public function testBuild(
        array $params,
    ): void {
        $builder = new OccurrenceSetBuilder();

        foreach ($params['occurrences'] as $occurrence) {
            $builder->add(
                startsAt: $occurrence['startsAt'],
                endsAt: $occurrence['endsAt'],
            );
        }

        $occurrenceSet = $builder->build();
        $occurrences = $occurrenceSet->items();

        foreach ($occurrences as $index => $occurrence) {
            $occurrenceTimeRange = $occurrence->getTimeRange();
            $startsAt = $occurrenceTimeRange->getStartsAt();
            $endsAt = $occurrenceTimeRange->getEndsAt();
            $occurrenceData = $params['occurrences'][$index];
            $this->assertEquals(
                expected: $startsAt->format(self::DATETIME_FORMAT),
                actual: $occurrenceData['startsAt'],
            );
            $this->assertEquals(
                expected: $endsAt->format(self::DATETIME_FORMAT),
                actual: $occurrenceData['endsAt'],
            );
        }

        $this->assertEquals(
            expected: count($params['occurrences']),
            actual: $occurrenceSet->count(),
        );
    }

    public function testBuildUsingTimeRangeOnly(): void
    {
        $builder = new OccurrenceSetBuilder();
        $builder->setTimeRange(
            timeRange: new TimeRange(
                startsAt: '2020-01-01 10:00:00',
                endsAt: '2020-01-01 11:00:00',
            ),
        );

        $this->assertCount(
            expectedCount: 1,
            haystack: $builder->build()->items(),
        );
    }

    public function testBuildWithExcluded(): void
    {
        $builder = new OccurrenceSetBuilder();
        $existingOccurrenceSet = new OccurrenceSet();
        $existingOccurrenceSet->add(
            new Occurrence(
                new TimeRange(
                    startsAt: '2020-01-01 10:00:00',
                    endsAt: '2020-01-01 11:00:00',
                ),
                new Status(
                    cancelled: true,
                ),
            ),
        );
        $builder->setExistingOccurrences($existingOccurrenceSet);
        $builder->add(
            startsAt: '2020-01-01 10:00:00',
            endsAt: '2020-01-01 11:00:00',
        );
        $occurrenceSet = $builder->build();

        $this->assertCount(
            expectedCount: 1,
            haystack: $occurrenceSet->items(),
        );
        $this->assertTrue($occurrenceSet->first()->getStatus()->isCancelled());
    }

    public function testBuildFromRule()
    {
        $limit = 7;

        $builder = new OccurrenceSetBuilder();
        $builder->setTimeRange(
            timeRange: new TimeRange(
                startsAt: '2020-01-01 10:00:00',
                endsAt: '2020-01-01 11:00:00',
            ),
        );
        $builder->setRule(
            rule: new RecurrenceRule(
                'FREQ=DAILY;INTERVAL=1',
            ),
        );
        $builder->setRecurrenceLimit(limit: $limit);

        $this->assertCount(
            expectedCount: $limit,
            haystack: $builder->build()->items(),
        );
    }

    public static function dataProviderForTestBuild(): Generator
    {
        yield 'Case #1' => [
            'params' => [
                'occurrences' => [
                    [
                        'startsAt' => '2020-01-01 10:00:00',
                        'endsAt' => '2020-01-01 11:00:00',
                    ],
                ],
            ],
        ];

        yield 'Case #2' => [
            'params' => [
                'occurrences' => [],
            ],
        ];
    }
}
