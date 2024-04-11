<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\V1\Booking;

use App\Entity\SpaceEntity;
use App\Utils\Testing\HasAuthenticatedClient;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateBookingControllerTest extends WebTestCase
{
    use HasAuthenticatedClient;

    private ?EntityManagerInterface $entityManager = null;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createAuthenticatedClient();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
    }

    public function testCreateBooking()
    {
        $spaceEntity = $this->getFirstActiveSpace();

        $startsAt = new DateTimeImmutable(datetime: '2095-01-01 15:00');
        $endsAt = new DateTimeImmutable(datetime: '2095-01-01 17:00');

        $this->client->request(
            method: 'POST',
            uri: 'booking',
            parameters: [
                'space' => $spaceEntity->getId(),
                'startsAt' => $startsAt->format(DATE_ATOM),
                'endsAt' => $endsAt->format(DATE_ATOM),
            ],
        );

        $data = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
            flags: JSON_UNESCAPED_SLASHES,
        );

        $this->assertResponseStatusCodeSame(
            expectedCode: Response::HTTP_CREATED,
        );
        $this->assertEquals(
            expected: $startsAt->format(DATE_ATOM),
            actual: $data['data']['startsAt'] ?? null,
        );
        $this->assertEquals(
            expected: $endsAt->format(DATE_ATOM),
            actual: $data['data']['endsAt'] ?? null,
        );
        $this->assertCount(
            expectedCount: 1,
            haystack: $data['data']['occurrences'] ?? [],
        );
    }

    public function testBookBusyTimeSlot()
    {
        $spaceEntity = $this->getFirstActiveSpace();

        $startsAt = new DateTimeImmutable(datetime: '2024-01-01 00:00');
        $endsAt = new DateTimeImmutable(datetime: '2024-01-01 24:00');

        $this->client->request(
            method: 'POST',
            uri: 'booking',
            parameters: [
                'space' => $spaceEntity->getId(),
                'startsAt' => $startsAt->format(DATE_ATOM),
                'endsAt' => $endsAt->format(DATE_ATOM),
            ],
        );

        $data = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
            flags: JSON_UNESCAPED_SLASHES,
        );

        $this->assertResponseStatusCodeSame(
            expectedCode: Response::HTTP_BAD_REQUEST,
        );
        $this->assertArrayHasKey(
            key: 'error',
            array: $data,
        );
        $this->assertSame(
            expected: 'Requested time slot from 21:00:00 to 22:00:00 on 2024-01-01 is not available',
            actual: $data['error'],
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    private function getFirstActiveSpace(): SpaceEntity
    {
        /** @var SpaceEntity $spaceEntity */
        $spaceEntity = $this
            ->entityManager
            ->getRepository(SpaceEntity::class)
            ->findOneBy(['active' => 1]);

        return $spaceEntity;
    }
}
