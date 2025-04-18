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
            'startDate' => '2025-04-20T10:00:00',
            'endDate' => '2025-04-21T18:00:00',
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
        $eventId = $GLOBALS['eventId'] ?? 1;

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
        ]));

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Updated Test Event', $response['title']);
    }

    public function testDeleteEvent(): void
    {
        $client = static::createClient();
        $eventId = $GLOBALS['eventId'] ?? 1;

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
}
