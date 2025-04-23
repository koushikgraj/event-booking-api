<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    public function testCreateEvent(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/events', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'title' => 'Test Event',
            'description' => 'This is a test event.',
            'country' => 'India',
            'capacity' => 100,
            'startDate' => '2025-04-20',
            'endDate' => '2025-04-21',
        ]));

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
        $this->assertSame('Test Event', $response['title']);

        // Save event ID for use in other tests (not persistent between methods without DB reset)
        $GLOBALS['eventId'] = $response['id'];
    }

    public function testListEvents(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/events');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testShowEvent(): void
    {
        $client = static::createClient();
        $eventId = $GLOBALS['eventId'] ?? 5;

        $client->request('GET', '/api/events/' . $eventId);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($eventId, $response['id']);
    }

    public function testUpdateEvent(): void
    {
        $client = static::createClient();
        $eventId = $GLOBALS['eventId'] ?? 1;

        $client->request('PUT', '/api/events/' . $eventId, [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'title' => 'Updated Test Event',
            'description'=>'abc',
            'country'=>'India',
            'capacity'=>'20',
            'startDate'=>'2025-05-01',
            'endDate'=>'2025-05-31'
        ]));

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Updated Test Event', $response['title']);
    }

    public function testDeleteEvent(): void
    {
        $client = static::createClient();
        $eventId = $GLOBALS['eventId'] ?? 3;

        $client->request('DELETE', '/api/events/' . $eventId);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Event deleted', $response['message']);
    }

    public function testShowEventNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/events/999999');

        $this->assertResponseStatusCodeSame(404);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Event not found', $response['error']);
    }

    public function testCreateEventWithMissingTitle(): void
    {
        $payload = [
            'description' => 'Missing title',
            'country' => 'India',
            'capacity' => 50,
            'startDate' => '2025-05-01T10:00:00',
            'endDate' => '2025-05-02T18:00:00',
        ];

        $this->client->request('POST', '/api/events', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['errors' => ['title' => 'This value should not be blank.']]);
    }

    public function testCreateEventWithInvalidDateFormat(): void
    {
        $payload = [
            'title' => 'Invalid Date',
            'description' => 'Invalid format',
            'country' => 'India',
            'capacity' => 20,
            'startDate' => '01-05-2025', // Invalid format
            'endDate' => '02-05-2025',
        ];

        $this->client->request('POST', '/api/events', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(500); // Parsing error or bad format
    }

    public function testCreateEventWithNegativeCapacity(): void
    {
        $client = self::createClient();
        $payload = [
            'title' => 'Negative Capacity',
            'description' => 'Invalid capacity',
            'country' => 'India',
            'capacity' => -10,
            'startDate' => '2025-05-01T10:00:00',
            'endDate' => '2025-05-02T18:00:00',
        ];

        $this->client->request('POST', '/api/events', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['errors' => ['capacity' => 'This value should be greater than or equal to 0.']]);
    }

}
