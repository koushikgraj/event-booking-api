<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingControllerTest extends WebTestCase
{
    private static int $eventId;
    private static int $attendeeId;

    public static function setUpBeforeClass(): void
    {
        // You can use Fixtures or set these manually if needed
        // We'll assume the event and attendee exist in the test DB
        self::$eventId = 1;      // Replace with a valid Event ID
        self::$attendeeId = 1;   // Replace with a valid Attendee ID
    }

    public function testCreateBooking(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'event_id' => self::$eventId,
            'attendee_id' => self::$attendeeId,
        ]));

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
    }

    public function testDuplicateBookingFails(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'event_id' => self::$eventId,
            'attendee_id' => self::$attendeeId,
        ]));

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Attendee already booked this event', $data['error']);
    }

    public function testCreateBookingInvalidEventOrAttendee(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'event_id' => 99999,     // Non-existent event
            'attendee_id' => 99999,  // Non-existent attendee
        ]));

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Event or Attendee not found', $data['error']);
    }

    public function testListBookings(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/bookings/');

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);

        if (!empty($data)) {
            $this->assertArrayHasKey('id', $data[0]);
            $this->assertArrayHasKey('attendee', $data[0]);
            $this->assertArrayHasKey('event', $data[0]);
        }
    }
}
