<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AttendeeControllerTest extends WebTestCase
{
    public function testCreateAttendeeSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/attendees', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('John Doe', $data['name']);
        $this->assertSame('john@example.com', $data['email']);
    }

    public function testCreateAttendeeValidationFailure(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/attendees', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'name' => '', // Empty name to trigger validation error
            'email' => 'not-an-email' // Invalid email format
        ]));

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('name', $data['errors']);
        $this->assertArrayHasKey('email', $data['errors']);
    }

    public function testListAttendees(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/attendees');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }
}
