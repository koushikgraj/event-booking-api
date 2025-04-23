<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Attendee;
use App\Entity\Event;
use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;

class BookingControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    // 1. Test Successful Booking Creation
    public function testCreateBookingSuccess(): void
    {
        $client = $this->client;

        // Create event
        $eventData = [
            'title' => 'Test Event',
            'description' => 'Description',
            'date' => (new \DateTime('+1 day'))->format('Y-m-d H:i:s'),
            'capacity' => 10,
        ];
        $client->request('POST', '/api/events', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($eventData));
        $eventResponse = json_decode($client->getResponse()->getContent(), true);
        $eventId = $eventResponse['id'] ?? null;

        // Create attendee
        $attendeeData = [
            'name' => 'Test Attendee',
            'email' => 'attendee@example.com',
        ];
        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($attendeeData));
        $attendeeResponse = json_decode($client->getResponse()->getContent(), true);
        $attendeeId = $attendeeResponse['id'] ?? null;

        // Create booking
        $bookingData = [
            'event_id' => $eventId,
            'attendee_id' => $attendeeId,
        ];
        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($bookingData));

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($eventId, $responseData['event']['id']);
        $this->assertEquals($attendeeId, $responseData['attendee']['id']);
    }


    // 2. Test Booking When Event Not Found
    public function testCreateBookingEventNotFound(): void
    {
        $client = $this->client;

        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => 99999,
            'attendee_id' => 1
        ]));

        $this->client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($data));
    
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['error' => 'Invalid event ID']);
    }

    // 3. Test Booking When Attendee Not Found
    public function testCreateBookingAttendeeNotFound(): void
    {
        $data = [
            'event_id' => 1,            // assuming event ID 1 exists
            'attendee_id' => 99999      // non-existent attendee
        ];
    
        $this->client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($data));
    
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['error' => 'Invalid attendee ID']);
    }

    // 4. Test Duplicate Booking
    public function testCreateBookingDuplicate(): void
    {
        $client = $this->client;
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $attendee = new Attendee();
        $attendee->setName('Duplicate');
        $attendee->setEmail('dup@example.com');
        $em->persist($attendee);

        $event = new Event();
        $event->setTitle('Test Event');
        $event->setDescription('This is a sample event description.');
        $event->setStartDate(new \DateTime('+1 day'));
        $event->setEndDate(new \DateTime('+10 day'));
        $event->setCountry('India');
        $event->setCapacity(100);
        $em->persist($event);

        $em->flush();

        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $event->getId(),
            'attendee_id' => $attendee->getId()
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    // 5. Test Overbooking
    public function testCreateBookingOverbooking(): void
    {
        $client = $this->client;
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $event = new Event();
        $event->setTitle('Full Event');
        $event->setDescription('This is a sample event description.');
        $event->setStartDate(new \DateTime('+1 day'));
        $event->setEndDate(new \DateTime('+10 day'));
        $event->setCountry('India');
        $event->setCapacity(100);
        $em->persist($event);

        $attendee1 = new Attendee();
        $attendee1->setName('A1');
        $attendee1->setEmail('a1@test.com');
        $em->persist($attendee1);

        $attendee2 = new Attendee();
        $attendee2->setName('A2');
        $attendee2->setEmail('a2@test.com');
        $em->persist($attendee2);
        
        $booking = new Booking();
        $booking->setEvent($event);
        $booking->setAttendee($attendee1);
        $em->persist($booking);

        $em->flush();

        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $event->getId(),
            'attendee_id' => $attendee2->getId()
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    // 6. Test Invalid Event Data Format
    public function testCreateBookingInvalidEventDataFormat(): void
    {
        $client = $this->client;

        // Sending invalid JSON
        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => 'invalid_format', // Not a valid event ID
            'attendee_id' => 1
        ]));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['error' => 'Invalid event ID']);
    }

    // 7. Test Missing Event ID in Request
    public function testCreateBookingMissingEventId(): void
    {
        $client = $this->client;

        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'attendee_id' => 1
        ]));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['error' => 'Missing event_id']);
    }

    // 8. Test Missing Attendee ID in Request
    public function testCreateBookingMissingAttendeeId(): void
    {
        $client = $this->client;

        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => 1
        ]));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['error' => 'Missing attendee_id']);
    }

    // 9. Test Invalid Attendee Email Format
    public function testCreateBookingInvalidAttendeeEmailFormat(): void
    {
        $client = $this->client;
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $event = new Event();
        $event->setTitle('Valid Event');
        $event->setDescription('This is a sample event description.');
        $event->setStartDate(new \DateTime('+1 day'));
        $event->setEndDate(new \DateTime('+10 day'));
        $event->setCountry('India');
        $event->setCapacity(100);
        $em->persist($event);

        $attendee = new Attendee();
        $attendee->setName('Test Attendee');
        $attendee->setEmail('invalid-email');
        $em->persist($attendee);

        $em->flush();

        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $event->getId(),
            'attendee_id' => $attendee->getId()
        ]));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['error' => 'Invalid email format']);
    }

    // 10. Test Event Capacity Updates After Booking
    public function testEventCapacityUpdateAfterBooking(): void
    {
        $client = $this->client;
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $event = new Event();
        $event->setTitle('Capacity Event');
        $event->setDescription('This is a sample event description.');
        $event->setStartDate(new \DateTime('+1 day'));
        $event->setEndDate(new \DateTime('+10 day'));
        $event->setCountry('India');
        $event->setCapacity(100);
        $em->persist($event);

        $attendee1 = new Attendee();
        $attendee1->setName('Attendee 1');
        $attendee1->setEmail('attendee1@test.com');
        $em->persist($attendee1);

        $em->flush();

        // Book for the first attendee
        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $event->getId(),
            'attendee_id' => $attendee1->getId()
        ]));

        $this->assertResponseIsSuccessful();

        // Test capacity update (it should now be 0)
        $client->request('GET', '/api/events/'.$event->getId());
        $this->assertResponseIsSuccessful();
        $eventData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(0, $eventData['capacity']); // Check that capacity is updated after booking
    }

    // 11. Test Cancelling a Booking
    public function testCancelBooking(): void
    {
        $client = $this->client;
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $event = new Event();
        $event->setTitle('Cancellable Event');
        $event->setDescription('This is a sample event description.');
        $event->setStartDate(new \DateTime('+1 day'));
        $event->setEndDate(new \DateTime('+10 day'));
        $event->setCountry('India');
        $event->setCapacity(100);
        $em->persist($event);

        $attendee = new Attendee();
        $attendee->setName('Attendee');
        $attendee->setEmail('attendee@test.com');
        $em->persist($attendee);

        $booking = new Booking();
        $booking->setEvent($event);
        $booking->setAttendee($attendee);
        $em->persist($booking);
        $em->flush();

        // Call DELETE request to cancel the booking
        $client->request('DELETE', '/api/bookings/'.$booking->getId());

        // Check that the booking has been deleted
        $client->request('GET', '/api/bookings/'.$booking->getId());
        $this->assertResponseStatusCodeSame(404); // Booking should not exist anymore
    }

    // 12. Test Booking for Future Event (Date Check)
    public function testCreateBookingForFutureEvent(): void
    {
        $client = $this->client;
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $event = new Event();
        $event->setTitle('Future Event');
        $event->setCapacity(5);
        $event->setCountry('India');
        $event->setDescription('This is a sample event description.');
        $event->setStartDate(new \DateTime('+1 week')); // Event scheduled for the future
        $event->setEndDate(new \DateTime('+10 day'));
        $em->persist($event);

        $attendee = new Attendee();
        $attendee->setName('Future Attendee');
        $attendee->setEmail('future@attendee.com');
        $em->persist($attendee);

        $em->flush();

        // Attempt to book for a future event
        $client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $event->getId(),
            'attendee_id' => $attendee->getId()
        ]));

        $this->assertResponseIsSuccessful();
    }

    public function testCreateBookingWithNonIntegerIds(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'eventId' => 'not-an-int',      // invalid event ID
                'attendeeId' => 'still-a-string' // invalid attendee ID
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('event_id and attendee_id required', $response['error']);
    }

    public function testCreateBookingWithMissingFields(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([]) // empty payload
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('event_id and attendee_id required', $response['error']);
    }
}
