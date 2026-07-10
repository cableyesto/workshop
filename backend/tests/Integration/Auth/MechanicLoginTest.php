<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Fixtures\SecurityFixtures;
use App\Tests\Traits\AuthenticationTrait;

/**
 * Integration tests for Mechanic PIN login flow
 *
 * Tests /api/garage/{siret}/mechanic/login endpoint
 */
class MechanicLoginTest extends ApiTestCase
{
    use AuthenticationTrait;

    /**
     * Test successful login with valid PIN and SIRET
     */
    public function testLoginWithValidPinReturnsToken(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => SecurityFixtures::MECHANIC_PIN,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($response, 200);

        $data = $this->getJsonResponse($response);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    /**
     * Test login fails when PIN is missing
     */
    public function testLoginWithMissingPinReturns400(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test login fails when SIRET is invalid
     */
    public function testLoginWithInvalidSiretReturns404(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/invalid/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => SecurityFixtures::MECHANIC_PIN,
            ])
        );

        $response = $this->client->getResponse();
        // Invalid SIRET format in URL → 404 or 401 depending on routing
        $this->assertContains($response->getStatusCode(), [401, 404]);
    }

    /**
     * Test login fails with non-existent garage SIRET
     */
    public function testLoginWithNonExistentGarageReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/99999999999999/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => SecurityFixtures::MECHANIC_PIN,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test login fails with wrong PIN
     */
    public function testLoginWithWrongPinReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => '9999', // Wrong PIN
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test login fails with invalid PIN format (too short)
     * Returns 401 because invalid format = authentication failure
     */
    public function testLoginWithTooShortPinReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => '123', // Only 3 digits
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test login fails with invalid PIN format (too long)
     * Returns 401 because invalid format = authentication failure
     */
    public function testLoginWithTooLongPinReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => '12345', // 5 digits
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test login fails with non-numeric PIN
     * Returns 401 because invalid format = authentication failure
     */
    public function testLoginWithNonNumericPinReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => 'abcd', // Not numeric
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test that JWT token contains mechanic ID
     */
    public function testTokenContainsMechanicId(): void
    {
        $token = $this->getMechanicToken();

        // Decode JWT payload
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'JWT should have 3 parts');

        $payload = json_decode(base64_decode($parts[1]), true);

        // Mechanic uses ID as identifier
        $this->assertArrayHasKey('username', $payload);
        $this->assertIsString($payload['username']);
    }

    /**
     * Test that JWT token contains ROLE_MECHANIC
     */
    public function testTokenContainsMechanicRole(): void
    {
        $token = $this->getMechanicToken();

        // Decode JWT payload
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('roles', $payload);
        $this->assertContains('ROLE_MECHANIC', $payload['roles']);
    }

    /**
     * Test that JWT token contains garage_id
     */
    public function testTokenContainsGarageId(): void
    {
        $token = $this->getMechanicToken();

        // Decode JWT payload
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'JWT should have 3 parts');

        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('garage_id', $payload);
        $this->assertIsInt($payload['garage_id']);
    }

    /**
     * Test that JWT token contains garage_siret
     */
    public function testTokenContainsGarageSiret(): void
    {
        $token = $this->getMechanicToken();

        // Decode JWT payload
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('garage_siret', $payload);
        $this->assertIsString($payload['garage_siret']);
        $this->assertEquals(SecurityFixtures::GARAGE_SIRET, $payload['garage_siret']);
    }

    /**
     * Test login with empty request body
     */
    public function testLoginWithEmptyBodyReturns400(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test login with malformed JSON
     */
    public function testLoginWithMalformedJsonReturns400(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{invalid json'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test mechanic from garage 2 cannot login to garage 1
     */
    public function testMechanicCannotLoginToWrongGarage(): void
    {
        $this->client->request(
            'POST',
            '/api/garage/' . SecurityFixtures::GARAGE2_SIRET . '/mechanic/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => SecurityFixtures::MECHANIC_PIN, // Garage 1 mechanic's PIN
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }
}
