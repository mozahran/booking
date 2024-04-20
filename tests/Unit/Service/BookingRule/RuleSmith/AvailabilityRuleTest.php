<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\BookingRule\RuleSmith;

use App\Contract\Service\BookingRule\RuleSmithInterface;
use App\Domain\DataObject\Rule\Availability;
use App\Domain\Enum\RuleType;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AvailabilityRuleTest extends KernelTestCase
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
        $ruleType = RuleType::AVAILABILITY;
        $normalized = [
            'daysBitmask' => $params['daysBitmask'],
            'start' => $params['start'],
            'end' => $params['end'],
            'spaceIds' => $params['spaceIds'],
        ];

        /** @var Availability $rule */
        $rule = $this->ruleSmith->parse(
            type: $ruleType,
            rule: json_encode($normalized),
        );

        $this->assertSame($rule->getType(), $ruleType);
        $this->assertSame($rule->getDaysBitmask(), $params['daysBitmask']);
        $this->assertSame($rule->getStartMinutes(), $params['start']);
        $this->assertSame($rule->getEndMinutes(), $params['end']);
        $this->assertSame($rule->getSpaceIds(), $params['spaceIds']);
        $this->assertSame($rule->normalize(), $normalized);
    }

    public static function dataProviderForTestParseRule(): Generator
    {
        yield 'Case #1' => [
            'params' => [
                'daysBitmask' => 127,
                'start' => 120,
                'end' => 240,
                'spaceIds' => null,
            ],
        ];

        yield 'Case #2' => [
            'params' => [
                'daysBitmask' => 2,
                'start' => 0,
                'end' => 120,
                'spaceIds' => [],
            ],
        ];

        yield 'Case #3' => [
            'params' => [
                'daysBitmask' => 8,
                'start' => 0,
                'end' => 120,
                'spaceIds' => [1, 2],
            ],
        ];
    }
}
