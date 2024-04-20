<?php

declare(strict_types=1);

namespace App\Tests\Unit\Builder;

use App\Builder\OccurrenceBuilder;
use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Booking\OccurrenceProxyMap;
use App\Domain\Exception\InvalidTimeRangeException;
use Generator;
use PHPUnit\Framework\TestCase;

class OccurrenceBuilderTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestBuild
     */
    public function testBuild(
        int $expectedCount,
        array $occurrences,
    ): void {
        $existingOccurrenceProxyMap = $this->buildOccurrences(
            occurrences: $occurrences['existing'],
        );
        $occurrenceProxyMap = $this->buildOccurrences(
            occurrences: $occurrences['new'],
            existingOccurrenceMapProxy: $existingOccurrenceProxyMap,
        );
        $this->assertCount(
            expectedCount: $expectedCount,
            haystack: $occurrenceProxyMap->items(),
        );
    }

    /**
     * @dataProvider dataProviderForTestBuildWithCopiedAttributesFromExistingOccurrences
     */
    public function testBuildWithCopiedAttributesFromExistingOccurrences(
        array $occurrences,
    ): void {
        $existingOccurrenceProxyMap = $this->buildOccurrences(
            occurrences: [$occurrences['existing']],
        );
        $occurrenceProxyMap = $this->buildOccurrences(
            occurrences: [$occurrences['new']],
            existingOccurrenceMapProxy: $existingOccurrenceProxyMap,
        );
        $existingOccurrences = $occurrenceProxyMap->items();
        /** @var Occurrence $existingOccurrence */
        $existingOccurrence = array_shift($existingOccurrences);
        $occurrences = $occurrenceProxyMap->items();
        /** @var Occurrence $occurrence */
        $occurrence = array_shift($occurrences);

        $this->assertSame($occurrence->getId(), $existingOccurrence->getId());
        $this->assertSame($occurrence->getBookingId(), $existingOccurrence->getBookingId());
        $this->assertSame($occurrence->isCancelled(), $existingOccurrence->isCancelled());
        $this->assertSame($occurrence->getCancellerId(), $existingOccurrence->getCancellerId());
    }

    public static function dataProviderForTestBuildWithCopiedAttributesFromExistingOccurrences(): Generator
    {
        yield 'Case #1' => [
            'occurrences' => [
                'existing' => [
                    'startsAt' => '2020-02-01 10:00',
                    'endsAt' => '2020-02-01 11:00',
                    'cancelled' => true,
                    'cancellerId' => 1,
                    'bookingId' => 1,
                    'id' => 1,
                ],
                'new' => [
                    'startsAt' => '2020-02-01 10:00',
                    'endsAt' => '2020-02-01 11:00',
                    'cancelled' => false,
                    'cancellerId' => null,
                    'bookingId' => null,
                    'id' => null,
                ],
            ],
        ];
    }

    public static function dataProviderForTestBuild(): Generator
    {
        yield 'Case #0' => [
            'expectedCount' => 0,
            'occurrences' => [
                'existing' => [],
                'new' => [],
            ],
        ];

        yield 'Case #1' => [
            'expectedCount' => 1,
            'occurrences' => [
                'existing' => [],
                'new' => [
                    [
                        'startsAt' => '2020-02-01 10:00',
                        'endsAt' => '2020-02-01 11:00',
                        'cancelled' => false,
                        'cancellerId' => null,
                        'bookingId' => null,
                        'id' => null,
                    ],
                ],
            ],
        ];

        yield 'Case #2' => [
            'expectedCount' => 1,
            'occurrences' => [
                'existing' => [
                    [
                        'startsAt' => '2020-02-01 10:00',
                        'endsAt' => '2020-02-01 11:00',
                        'cancelled' => false,
                        'cancellerId' => null,
                        'bookingId' => null,
                        'id' => null,
                    ],
                ],
                'new' => [],
            ],
        ];

        yield 'Case #3' => [
            'expectedCount' => 2,
            'occurrences' => [
                'existing' => [
                    [
                        'startsAt' => '2020-01-01 10:00',
                        'endsAt' => '2020-01-01 11:00',
                        'cancelled' => false,
                        'cancellerId' => null,
                        'bookingId' => null,
                        'id' => null,
                    ],
                ],
                'new' => [
                    [
                        'startsAt' => '2020-02-01 10:00',
                        'endsAt' => '2020-02-01 11:00',
                        'cancelled' => false,
                        'cancellerId' => null,
                        'bookingId' => null,
                        'id' => null,
                    ],
                ],
            ],
        ];

        yield 'Case #4' => [
            'expectedCount' => 1,
            'occurrences' => [
                'existing' => [
                    [
                        'startsAt' => '2020-01-01 10:00',
                        'endsAt' => '2020-01-01 11:00',
                        'cancelled' => false,
                        'cancellerId' => null,
                        'bookingId' => null,
                        'id' => null,
                    ],
                ],
                'new' => [
                    [
                        'startsAt' => '2020-01-01 10:00',
                        'endsAt' => '2020-01-01 11:00',
                        'cancelled' => false,
                        'cancellerId' => null,
                        'bookingId' => null,
                        'id' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws InvalidTimeRangeException
     */
    private function buildOccurrences(
        array $occurrences,
        ?OccurrenceProxyMap $existingOccurrenceMapProxy = null,
    ): OccurrenceProxyMap {
        $existingOccurrenceMapProxy = $existingOccurrenceMapProxy ?? OccurrenceProxyMap::empty();
        $occurrenceBuilder = new OccurrenceBuilder(
            existingOccurrences: $existingOccurrenceMapProxy,
        );
        foreach ($occurrences as $occurrence) {
            $occurrenceBuilder->add(
                startsAt: $occurrence['startsAt'],
                endsAt: $occurrence['endsAt'],
                cancelled: $occurrence['cancelled'],
                cancellerId: $occurrence['cancellerId'],
                bookingId: $occurrence['bookingId'],
                id: $occurrence['id'],
            );
        }

        return $occurrenceBuilder->build();
    }
}
