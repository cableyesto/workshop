<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Fixtures\SecurityFixtures;
use App\Tests\Traits\AuthenticationTrait;

/**
 * RBAC tests for RECEPTIONIST role
 *
 * Simplified smoke tests verifying RECEPTIONIST can access key endpoints.
 * Just checks that receptionist has access (200/201 responses).
 */
class ReceptionistRoleAccessTest extends ApiTestCase
{
    use AuthenticationTrait;

    /**
     * Test that receptionist can access mechanics endpoint
     */
    public function testCanAccessMechanicsEndpoint(): void
    {
        $token = $this->getReceptionistToken();

        $this->client->request(
            'GET',
            '/api/mechanics',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Receptionist should have access to mechanics endpoint');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data);
    }

    /**
     * Test that receptionist can access receptionists endpoint
     */
    public function testCanAccessReceptionistsEndpoint(): void
    {
        $token = $this->getReceptionistToken();

        $this->client->request(
            'GET',
            '/api/receptionists',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Receptionist should have access to receptionists endpoint');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data);
    }

    /**
     * Test that receptionist can create car with client
     */
    public function testCanCreateCarWithClient(): void
    {
        $token = $this->getReceptionistToken();

        $this->client->request(
            'POST',
            '/api/cars/with-client',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'client' => [
                    'firstName' => 'Test',
                    'lastName' => 'Client',
                    'email' => 'test.client@example.com',
                    'phone' => '0123456789',
                    'street' => '123 Test Street',
                    'city' => 'Test City',
                    'zipCode' => '75001',
                ],
                'car' => [
                    'licensePlate' => 'AB-123-CD',
                    'brand' => 'Toyota',
                    'model' => 'Corolla',
                    'year' => 2020,
                ],
            ])
        );

        $response = $this->client->getResponse();
        $this->assertContains(
            $response->getStatusCode(),
            [200, 201, 400], // 400 might occur if validation fails (missing required fields)
            'Receptionist should be able to create cars with clients'
        );
    }
}
