<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\BookingRule\RuleSmith;

use App\Contract\Service\BookingRule\RuleSmithInterface;
use App\Domain\DataObject\Rule\Availability;
use App\Domain\DataObject\Rule\Condition;
use App\Domain\DataObject\Rule\ConditionGroup;
use App\Domain\DataObject\Rule\Deny;
use App\Domain\Enum\Rule\Operand;
use App\Domain\Enum\Rule\Operator;
use App\Domain\Enum\RuleType;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DenyRuleTest extends KernelTestCase
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
        $ruleType = RuleType::DENY;
        $normalized = [
            'daysBitmask' => $params['daysBitmask'],
            'start' => $params['start'],
            'end' => $params['end'],
            'g' => [
                [
                    'conditions' => [
                        [
                            'operand' => $params['g'][0]['conditions'][0]['operand'],
                            'operator' => $params['g'][0]['conditions'][0]['operator'],
                            'value' => $params['g'][0]['conditions'][0]['value'],
                        ],
                    ],
                ],
            ],
            'spaceIds' => $params['spaceIds'],
        ];

        /** @var Deny $rule */
        $rule = $this->ruleSmith->parse(
            type: $ruleType,
            rule: json_encode($normalized),
        );

        $this->assertSame($rule->getType(), $ruleType);
        $this->assertSame($rule->getDaysBitmask(), $params['daysBitmask']);
        $this->assertSame($rule->getStartMinutes(), $params['start']);
        $this->assertSame($rule->getEndMinutes(), $params['end']);
        $this->assertSame($rule->getSpaceIds(), $params['spaceIds']);
        $this->assertSame(
            $rule->getConditionGroups()[0]->getConditions()[0]->getOperand()->value,
            $params['g'][0]['conditions'][0]['operand'],
        );
        $this->assertSame(
            $rule->getConditionGroups()[0]->getConditions()[0]->getOperator()->value,
            $params['g'][0]['conditions'][0]['operator'],
        );
        $this->assertSame(
            $rule->getConditionGroups()[0]->getConditions()[0]->getValue(),
            $params['g'][0]['conditions'][0]['value'],
        );
        $this->assertSame(
            $rule->normalize(),
            $normalized
        );
    }

    public static function dataProviderForTestParseRule(): Generator
    {
        yield 'Case #1' => [
            'params' => [
                'daysBitmask' => 127,
                'start' => 120,
                'end' => 240,
                'spaceIds' => null,
                'g' => [
                    [
                        'conditions' => [
                            [
                                'operand' => Operand::ITS_DURATION->value,
                                'operator' => Operator::EQUAL_TO->value,
                                'value' => 60,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Case #2' => [
            'params' => [
                'daysBitmask' => 2,
                'start' => 0,
                'end' => 120,
                'spaceIds' => [],
                'g' => [
                    [
                        'conditions' => [
                            [
                                'operand' => Operand::ITS_DURATION->value,
                                'operator' => Operator::EQUAL_TO->value,
                                'value' => 60,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Case #3' => [
            'params' => [
                'daysBitmask' => 8,
                'start' => 0,
                'end' => 120,
                'spaceIds' => [1, 2],
                'g' => [
                    [
                        'conditions' => [
                            [
                                'operand' => Operand::ITS_DURATION->value,
                                'operator' => Operator::EQUAL_TO->value,
                                'value' => 60,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
