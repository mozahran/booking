<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\BookingRule\RuleSmith;

use App\Contract\Service\BookingRule\RuleSmithInterface;
use App\Domain\DataObject\Rule\Availability;
use App\Domain\DataObject\Rule\Buffer;
use App\Domain\Enum\RuleType;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BufferRuleTest extends KernelTestCase
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
        $ruleType = RuleType::BUFFER;
        $normalized = [
            'value' => $params['value'],
            'spaceIds' => $params['spaceIds'],
        ];

        /** @var Buffer $rule */
        $rule = $this->ruleSmith->parse(
            type: $ruleType,
            rule: json_encode($normalized),
        );

        $this->assertSame($rule->getType(), $ruleType);
        $this->assertSame($rule->getValue(), $params['value']);
        $this->assertSame($rule->getSpaceIds(), $params['spaceIds']);
        $this->assertSame($rule->normalize(), $normalized);
    }

    public static function dataProviderForTestParseRule(): Generator
    {
        yield 'Case #1' => [
            'params' => [
                'value' => 240,
                'spaceIds' => null,
            ],
        ];

        yield 'Case #2' => [
            'params' => [
                'value' => 120,
                'spaceIds' => [],
            ],
        ];

        yield 'Case #3' => [
            'params' => [
                'value' => 120,
                'spaceIds' => [1, 2],
            ],
        ];
    }
}
