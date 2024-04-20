<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\BookingRule\RuleSmith;

use App\Contract\Service\BookingRule\RuleSmithInterface;
use App\Domain\DataObject\Rule\Availability;
use App\Domain\DataObject\Rule\Quota;
use App\Domain\DataObject\Rule\Window;
use App\Domain\Enum\Rule\AggregationMetric;
use App\Domain\Enum\Rule\Mode;
use App\Domain\Enum\Rule\Period;
use App\Domain\Enum\Rule\Predicate;
use App\Domain\Enum\RuleType;
use App\Domain\Enum\UserRole;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WindowRuleTest extends KernelTestCase
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
        $ruleType = RuleType::WINDOW;
        $normalized = [
            'predicate' => $params['predicate'],
            'value' => $params['value'],
            'roles' => $params['roles'],
            'spaceIds' => $params['spaceIds'],
        ];

        /** @var Window $rule */
        $rule = $this->ruleSmith->parse(
            type: $ruleType,
            rule: json_encode($normalized),
        );

        $this->assertSame($rule->getType(), $ruleType);
        $this->assertSame($rule->getPredicate()->value, $params['predicate']);
        $this->assertSame($rule->getValue(), $params['value']);
        $this->assertSame($rule->getRoles(), $params['roles']);
        $this->assertSame($rule->getSpaceIds(), $params['spaceIds']);
        $this->assertSame($rule->normalize(), $normalized);
    }

    public static function dataProviderForTestParseRule(): Generator
    {
        yield 'Case #1' => [
            'params' => [
                'predicate' => Predicate::MORE_THAN_INCLUDING_TODAY->value,
                'value' => 60,
                'roles' => null,
                'spaceIds' => null,
            ],
        ];

        yield 'Case #2' => [
            'params' => [
                'predicate' => Predicate::MORE_THAN_INCLUDING_TODAY->value,
                'value' => 60,
                'roles' => [
                    UserRole::OWNER->value,
                    UserRole::ADMIN->value,
                ],
                'spaceIds' => [1, 2],
            ],
        ];
    }
}
