<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Booking\Gatekeeper;

use App\Contract\Service\RuleSmithInterface;
use App\Domain\Enum\RuleType;
use App\Domain\Exception\RuleViolationException;
use App\Repository\BookingRepository;
use App\Service\Gatekeeper;
use App\Tests\Utils\TestBookingFactory;
use App\Validator\BufferRuleValidator;
use DateTimeImmutable;
use DateTimeInterface;
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
        $gatekeeper = $this->getGatekeeper($params);

        if (true === $throwsException) {
            $this->expectException(RuleViolationException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $now = new DateTimeImmutable();
        $afterAnHour = new DateTimeImmutable('+1 hour');

        $newBooking = TestBookingFactory::createSingleOccurrenceBooking(
            startsAt: $now->format(DateTimeInterface::ATOM),
            endsAt: $afterAnHour->format(DateTimeInterface::ATOM),
            spaceId: $params['bookingSpaceId'],
        );

        $gatekeeper->validate(
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
                'bookingSpaceId' => 1,
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
                'bookingSpaceId' => 1,
                'hasBufferConflict' => true,
                'rules' => [
                    $ruleSmith->parse(
                        type: RuleType::BUFFER,
                        rule: '{"value":60,"spaceIds":[2]}',
                    ),
                ],
            ],
        ];

        yield 'CASE #02, no exception, space matches' => [
            'throwsException' => false,
            'params' => [
                'bookingSpaceId' => 1,
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
