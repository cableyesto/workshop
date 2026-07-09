<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Fixtures\SecurityFixtures;
use App\Tests\Traits\AuthenticationTrait;

/**
 * RBAC tests for MECHANIC role
 *
 * Verifies that MECHANIC cannot access restricted endpoints.
 * Tests what mechanics are NOT allowed to do.
 */
class MechanicRoleAccessTest extends ApiTestCase
{
    use AuthenticationTrait;

    // ====================
    // MECHANICS MANAGEMENT (4 tests)
    // ====================

    /**
     * Test that mechanic cannot view mechanics list
     */
    public function testCannotViewMechanics(): void
    {
        $token = $this->getMechanicToken();

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
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to view mechanics list');
    }

    /**
     * Test that mechanic cannot create another mechanic
     */
    public function testCannotCreateMechanic(): void
    {
        $token = $this->getMechanicToken();

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
                'birthDate' => '1990-01-01',
                'pin' => '5678',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to create mechanics');
    }

    /**
     * Test that mechanic cannot update another mechanic
     */
    public function testCannotUpdateMechanic(): void
    {
        $token = $this->getMechanicToken();

        $this->client->request(
            'PUT',
            '/api/mechanics/999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'Updated',
                'lastName' => 'Name',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to update mechanics');
    }

    /**
     * Test that mechanic cannot delete another mechanic
     */
    public function testCannotDeleteMechanic(): void
    {
        $token = $this->getMechanicToken();

        $this->client->request(
            'DELETE',
            '/api/mechanics/999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to delete mechanics');
    }

    // ====================
    // RECEPTIONISTS MANAGEMENT (4 tests)
    // ====================

    /**
     * Test that mechanic cannot view receptionists list
     */
    public function testCannotViewReceptionists(): void
    {
        $token = $this->getMechanicToken();

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
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to view receptionists list');
    }

    /**
     * Test that mechanic cannot create a receptionist
     */
    public function testCannotCreateReceptionist(): void
    {
        $token = $this->getMechanicToken();

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
                'birthDate' => '1990-01-01',
                'email' => 'new.receptionist@test.com',
                'password' => 'password123',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to create receptionists');
    }

    /**
     * Test that mechanic cannot update a receptionist
     */
    public function testCannotUpdateReceptionist(): void
    {
        $token = $this->getMechanicToken();

        $this->client->request(
            'PUT',
            '/api/receptionists/999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'Updated',
                'lastName' => 'Name',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to update receptionists');
    }

    /**
     * Test that mechanic cannot delete a receptionist
     */
    public function testCannotDeleteReceptionist(): void
    {
        $token = $this->getMechanicToken();

        $this->client->request(
            'DELETE',
            '/api/receptionists/999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to delete receptionists');
    }

    // ====================
    // CLIENTS MANAGEMENT (2 tests)
    // ====================

    /**
     * Test that mechanic cannot update a client
     */
    public function testCannotUpdateClient(): void
    {
        $token = $this->getMechanicToken();

        $this->client->request(
            'PUT',
            '/api/clients/999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'firstName' => 'Updated',
                'lastName' => 'Client',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to update clients');
    }

    /**
     * Test that mechanic cannot update client callback status
     */
    public function testCannotUpdateClientCallback(): void
    {
        $token = $this->getMechanicToken();

        $this->client->request(
            'PATCH',
            '/api/clients/999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'calledBack' => true,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to update client callback status');
    }

    // ====================
    // INTERVENTIONS (1 test)
    // ====================

    /**
     * Test that mechanic cannot create an intervention
     * Note: Receptionists create interventions, mechanics execute them
     */
    public function testCannotCreateIntervention(): void
    {
        $token = $this->getMechanicToken();

        $this->client->request(
            'POST',
            '/api/interventions',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'carId' => 1,
                'description' => 'Test intervention',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Mechanic should not be able to create interventions');
    }
}
