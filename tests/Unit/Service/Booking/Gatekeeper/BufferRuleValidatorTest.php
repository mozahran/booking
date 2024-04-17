<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Booking\Gatekeeper;

use App\Contract\Service\RuleSmithInterface;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleViolationException;
use App\Repository\BookingRepository;
use App\Service\Gatekeeper;
use App\Tests\Utils\TestBookingFactory;
use App\Utils\DateSmith;
use App\Validator\BufferRuleValidator;
use Generator;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BufferRuleValidatorTest extends KernelTestCase
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

        $newBooking = TestBookingFactory::createSingleOccurrenceBooking(
            startsAt: $params['startsAt'],
            endsAt: $params['endsAt'],
            spaceId: $params['spaceId'],
            userId: $params['userId'],
        );

        $this->getGatekeeper($params)->validate(
            rules: $params['rules'],
            booking: $newBooking,
        );
    }

    public static function dataProviderForTestRule(): Generator
    {
        /** @var RuleSmithInterface $ruleSmith */
        $ruleSmith = self::getContainer()->get(RuleSmithInterface::class);

        yield 'CASE #01, throws exception, space matches' => [
            'throwsException' => true,
            'params' => [
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'spaceId' => 1,
                'userId' => 1,
                'hasBufferConflict' => true,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::BUFFER,
                        rule: '{"value":60,"spaceIds":null}',
                    ),
                ],
            ],
        ];

        yield 'CASE #02, no exception, space mismatches' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'spaceId' => 1,
                'userId' => 1,
                'hasBufferConflict' => true,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::BUFFER,
                        rule: '{"value":60,"spaceIds":[2]}',
                    ),
                ],
            ],
        ];

        yield 'CASE #03, no exception, space matches' => [
            'throwsException' => false,
            'params' => [
                'startsAt' => DateSmith::withTime(22, 0),
                'endsAt' => DateSmith::withTime(23, 0),
                'spaceId' => 1,
                'userId' => 1,
                'hasBufferConflict' => false,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::BUFFER,
                        rule: '{"value":60,"spaceIds":[1]}',
                    ),
                ],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    private function getGatekeeper(
        array $params,
    ): Gatekeeper {
        $bookingRepositoryMock = $this->createMock(BookingRepository::class);
        $bookingRepositoryMock
            ->method('countBufferConflicts')
            ->willReturn((int)$params['hasBufferConflict']);

        $bufferRuleValidator = new BufferRuleValidator(
            bookingRepository: $bookingRepositoryMock,
        );

        return new Gatekeeper(
            ruleValidators: [
                $bufferRuleValidator,
            ],
        );
    }
}
