<?php

namespace App\Tests\Functional\Controller\V1\Booking;

use App\Utils\Testing\HasAuthenticatedClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShowBookingControllerTest extends WebTestCase
{
    use HasAuthenticatedClient;

    public function testShowBooking(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'booking/1');

        $data = '{"data":{"id":1,"userId":1,"spaceId":1,"startsAt":"2024-01-01T21:00:00+00:00","endsAt":"2024-01-01T22:00:00+00:00","duration":60,"recurrenceRule":null,"excludedDates":[],"occurrences":[{"id":1,"bookingId":1,"startsAt":"2024-01-01T21:00:00+00:00","endsAt":"2024-01-01T22:00:00+00:00","duration":60,"cancelled":false,"cancellerId":null}],"cancelled":false,"cancellerId":null,"repeatable":false}}';

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            expected: $client->getResponse()->getContent(),
            actual: $data,
        );
    }
}
