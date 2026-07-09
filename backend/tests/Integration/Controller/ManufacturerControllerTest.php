<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Traits\AuthenticationTrait;

/**
 * Integration tests for ManufacturerController
 *
 * Tests basic operations for manufacturer reference data.
 * Note: Only list endpoint exists (no by-ID or search routes).
 */
class ManufacturerControllerTest extends ApiTestCase
{
    use AuthenticationTrait;

    /**
     * Test that getting all manufacturers returns 200 OK
     */
    public function testGetAllManufacturersReturns200(): void
    {
        $token = $this->getOwnerToken();

        $this->client->request(
            'GET',
            '/api/manufacturers',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Should be able to retrieve manufacturers list');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data, 'Manufacturers response should be an array');
        $this->assertNotEmpty($data, 'Manufacturers list should not be empty');
    }

    /**
     * Test that manufacturers endpoint requires authentication
     */
    public function testManufacturersRequireAuthentication(): void
    {
        // Request without token
        $this->client->request(
            'GET',
            '/api/manufacturers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode(), 'Manufacturers endpoint should require authentication');
    }
}
