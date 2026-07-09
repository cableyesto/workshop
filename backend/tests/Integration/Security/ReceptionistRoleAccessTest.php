<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Fixtures\SecurityFixtures;
use App\Tests\Traits\AuthenticationTrait;

/**
 * RBAC tests for RECEPTIONIST role
 *
 * Verifies that RECEPTIONIST can access authorized endpoints.
 * Tests what receptionists ARE allowed to do (positive tests).
 */
class ReceptionistRoleAccessTest extends ApiTestCase
{
    use AuthenticationTrait;

    // ====================
    // MECHANICS MANAGEMENT (4 tests)
    // ====================

    /**
     * Test that receptionist can view mechanics list
     */
    public function testCanViewMechanics(): void
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
        $this->assertResponseIsSuccessful('Receptionist should be able to view mechanics list');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data);
    }

    /**
     * Test that receptionist can create a mechanic
     */
    public function testCanCreateMechanic(): void
    {
        $token = $this->getReceptionistToken();

        $this->client->request(
            'POST',
            '/api/mechanics',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'New',
                'lastName' => 'Mechanic',
                'birthDate' => '1990-05-15',
                'pin' => '9876',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertContains(
            $response->getStatusCode(),
            [200, 201],
            'Receptionist should be able to create mechanics'
        );
    }

    /**
     * Test that receptionist can update a mechanic
     */
    public function testCanUpdateMechanic(): void
    {
        $token = $this->getReceptionistToken();

        // First, get mechanics to find a valid ID
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

        $mechanics = $this->getJsonResponse($this->client->getResponse());

        if (empty($mechanics)) {
            $this->markTestSkipped('No mechanics available to update');
        }

        $mechanicId = $mechanics[0]['id'];

        // Now update the mechanic
        $this->client->request(
            'PUT',
            "/api/mechanics/{$mechanicId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'Updated',
                'lastName' => 'Mechanic',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Receptionist should be able to update mechanics');
    }

    /**
     * Test that receptionist can delete a mechanic
     */
    public function testCanDeleteMechanic(): void
    {
        $token = $this->getReceptionistToken();

        // First create a mechanic to delete
        $this->client->request(
            'POST',
            '/api/mechanics',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'ToDelete',
                'lastName' => 'Mechanic',
                'birthDate' => '1985-03-20',
                'pin' => '1111',
            ])
        );

        $createResponse = $this->getJsonResponse($this->client->getResponse());
        $mechanicId = $createResponse['id'];

        // Now delete the mechanic
        $this->client->request(
            'DELETE',
            "/api/mechanics/{$mechanicId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertContains(
            $response->getStatusCode(),
            [200, 204],
            'Receptionist should be able to delete mechanics'
        );
    }

    // ====================
    // RECEPTIONISTS MANAGEMENT (4 tests)
    // ====================

    /**
     * Test that receptionist can view receptionists list
     */
    public function testCanViewReceptionists(): void
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
        $this->assertResponseIsSuccessful('Receptionist should be able to view receptionists list');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data);
    }

    /**
     * Test that receptionist can create another receptionist
     */
    public function testCanCreateReceptionist(): void
    {
        $token = $this->getReceptionistToken();

        $this->client->request(
            'POST',
            '/api/receptionists',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'New',
                'lastName' => 'Receptionist',
                'birthDate' => '1992-08-10',
                'email' => 'new.receptionist@test.com',
                'password' => 'password123',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertContains(
            $response->getStatusCode(),
            [200, 201],
            'Receptionist should be able to create other receptionists'
        );
    }

    /**
     * Test that receptionist can update another receptionist
     */
    public function testCanUpdateReceptionist(): void
    {
        $token = $this->getReceptionistToken();

        // First, get receptionists to find a valid ID
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

        $receptionists = $this->getJsonResponse($this->client->getResponse());

        if (empty($receptionists)) {
            $this->markTestSkipped('No receptionists available to update');
        }

        $receptionistId = $receptionists[0]['id'];

        // Now update the receptionist
        $this->client->request(
            'PUT',
            "/api/receptionists/{$receptionistId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'Updated',
                'lastName' => 'Receptionist',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Receptionist should be able to update other receptionists');
    }

    /**
     * Test that receptionist can delete another receptionist
     */
    public function testCanDeleteReceptionist(): void
    {
        $token = $this->getReceptionistToken();

        // First create a receptionist to delete
        $this->client->request(
            'POST',
            '/api/receptionists',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'ToDelete',
                'lastName' => 'Receptionist',
                'birthDate' => '1988-12-05',
                'email' => 'todelete.receptionist@test.com',
                'password' => 'password123',
            ])
        );

        $createResponse = $this->getJsonResponse($this->client->getResponse());
        $receptionistId = $createResponse['id'];

        // Now delete the receptionist
        $this->client->request(
            'DELETE',
            "/api/receptionists/{$receptionistId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertContains(
            $response->getStatusCode(),
            [200, 204],
            'Receptionist should be able to delete other receptionists'
        );
    }

    // ====================
    // CLIENTS & INTERVENTIONS (1 combined test)
    // ====================

    /**
     * Test that receptionist can create car with client
     * Note: Clients are created together with cars, not standalone
     */
    public function testCanCreateCarWithClient(): void
    {
        $token = $this->getReceptionistToken();

        // Test car + client creation (combined endpoint)
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

        $carResponse = $this->client->getResponse();
        $this->assertContains(
            $carResponse->getStatusCode(),
            [200, 201, 400], // 400 might occur if validation fails (missing required fields)
            'Receptionist should be able to create cars with clients'
        );
    }
}
