<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Fixtures\SecurityFixtures;
use App\Tests\Traits\AuthenticationTrait;

/**
 * RBAC tests for OWNER role
 *
 * Smoke tests verifying OWNER can access key resources.
 * OWNER has full access - these tests just verify basic access works.
 */
class OwnerRoleAccessTest extends ApiTestCase
{
    use AuthenticationTrait;

    /**
     * Test that owner can manage mechanics
     * Smoke test for mechanics endpoint access
     */
    public function testCanManageMechanics(): void
    {
        $token = $this->getOwnerToken();

        // Test GET mechanics
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
        $this->assertResponseIsSuccessful('Owner should have access to mechanics management');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data);
    }

    /**
     * Test that owner can manage receptionists
     * Smoke test for receptionists endpoint access
     */
    public function testCanManageReceptionists(): void
    {
        $token = $this->getOwnerToken();

        // Test GET receptionists
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
        $this->assertResponseIsSuccessful('Owner should have access to receptionists management');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data);
    }

    /**
     * Test that owner can access garage information
     * Smoke test for garage endpoint access
     */
    public function testCanManageGarage(): void
    {
        $token = $this->getOwnerToken();

        // Decode JWT to get garage_ids
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);
        $garageIds = $payload['garage_ids'] ?? [];

        if (empty($garageIds)) {
            $this->markTestSkipped('Owner has no garages in JWT payload');
        }

        $garageId = $garageIds[0];

        // Test GET garage
        $this->client->request(
            'GET',
            "/api/garages/{$garageId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Owner should have access to their garage information');

        $data = $this->getJsonResponse($response);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($garageId, $data['id']);
    }
}
