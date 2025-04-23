<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AttendeeControllerTest extends WebTestCase
{
    // Test Create Attendee with Valid Data
    public function testCreateAttendee(): void
{
    $client = static::createClient();

    $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
    ]));

    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Type', 'application/json');
    
    $responseContent = json_decode($client->getResponse()->getContent(), true);
    $this->assertArrayHasKey('id', $responseContent);
    $this->assertSame('John Doe', $responseContent['name']);
    $this->assertSame('john.doe@example.com', $responseContent['email']);
}


    // Test Create Attendee with Missing Name
    public function testCreateAttendeeWithMissingName()
    {
        $client = static::createClient();

        $data = [
            'email' => 'missing.name@example.com'
        ];

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('name', $responseContent);
    }

    // Test Create Attendee with Invalid Email
    public function testCreateAttendeeWithInvalidEmail()
    {
        $client = static::createClient();

        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email'
        ];

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('email', $responseContent);
    }

    // Test List All Attendees
    public function testListAttendees()
    {
        $client = static::createClient();

        $client->request('GET', '/api/attendees');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }

    // Test Create Attendee with Empty Request Body
    public function testCreateAttendeeWithEmptyRequestBody()
    {
        $client = static::createClient();

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], '');

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
    }

    // Test Create Attendee with Empty Email
    public function testCreateAttendeeWithEmptyEmail()
    {
        $client = static::createClient();

        $data = [
            'name' => 'John Doe',
            'email' => ''
        ];

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);
    }

    // Test List Attendees When No Attendees Exist
    public function testListAttendeesWhenNoAttendeesExist(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/attendees');

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        // Assert that the response is an empty array
        $this->assertIsArray($responseData);
        $this->assertCount(0, $responseData);
    }


    // Test Create Attendee with Duplicate Email
    public function testCreateAttendeeWithDuplicateEmail()
    {
        $client = static::createClient();

        // First attendee
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ];
        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // Attempt to create another attendee with the same email
        $data = [
            'name' => 'Jane Doe',
            'email' => 'john.doe@example.com'
        ];
        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);
    }

    // Test Create Attendee with Only Name
    public function testCreateAttendeeWithOnlyName()
    {
        $client = static::createClient();

        $data = [
            'name' => 'John Doe'
        ];

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);
    }

    // Test Create Attendee with Only Email
    public function testCreateAttendeeWithOnlyEmail()
    {
        $client = static::createClient();

        $data = [
            'email' => 'john.doe@example.com'
        ];

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('name', $responseData['errors']);
    }
}
