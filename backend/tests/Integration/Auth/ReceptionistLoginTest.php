<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Fixtures\SecurityFixtures;
use App\Tests\Traits\AuthenticationTrait;

/**
 * Integration tests for Receptionist login flow
 *
 * Tests /api/garage/{siret}/receptionist/login endpoint
 */
class ReceptionistLoginTest extends ApiTestCase
{
    use AuthenticationTrait;

    /**
     * Test successful login with valid credentials + SIRET
     * Note: Receptionist uses /api/login but requires SIRET in request body
     */
    public function testLoginWithValidCredentialsReturnsToken(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => SecurityFixtures::RECEPTIONIST_EMAIL,
                'password' => SecurityFixtures::RECEPTIONIST_PASSWORD,
                'siret' => SecurityFixtures::GARAGE_SIRET,
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
     * Test login fails when SIRET is missing (Receptionist-specific requirement)
     */
    public function testLoginWithMissingSiretReturns400(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => SecurityFixtures::RECEPTIONIST_EMAIL,
                'password' => SecurityFixtures::RECEPTIONIST_PASSWORD,
                // Missing siret
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $data = $this->getJsonResponse($response);
        $this->assertStringContainsString('SIRET', $data['error']);
    }

    /**
     * Test login fails with wrong SIRET
     */
    public function testLoginWithWrongSiretReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => SecurityFixtures::RECEPTIONIST_EMAIL,
                'password' => SecurityFixtures::RECEPTIONIST_PASSWORD,
                'siret' => '99999999999999', // Wrong SIRET
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test login fails when email is missing
     */
    public function testLoginWithMissingEmailReturns400(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'password' => SecurityFixtures::RECEPTIONIST_PASSWORD,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test login fails when password is missing
     */
    public function testLoginWithMissingPasswordReturns400(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => SecurityFixtures::RECEPTIONIST_EMAIL,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test login fails with wrong email
     */
    public function testLoginWithWrongEmailReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'wrong@example.com',
                'password' => SecurityFixtures::RECEPTIONIST_PASSWORD,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test login fails with wrong password
     */
    public function testLoginWithWrongPasswordReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => SecurityFixtures::RECEPTIONIST_EMAIL,
                'password' => 'wrongpassword',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test that JWT token contains garage_id
     */
    public function testTokenContainsGarageId(): void
    {
        $token = $this->getReceptionistToken();

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
        $token = $this->getReceptionistToken();

        // Decode JWT payload
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('garage_siret', $payload);
        $this->assertEquals(SecurityFixtures::GARAGE_SIRET, $payload['garage_siret']);
    }

    /**
     * Test that JWT token contains ROLE_RECEPTIONIST
     */
    public function testTokenContainsReceptionistRole(): void
    {
        $token = $this->getReceptionistToken();

        // Decode JWT payload
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('roles', $payload);
        $this->assertContains('ROLE_RECEPTIONIST', $payload['roles']);
    }

    /**
     * Test login with empty request body
     */
    public function testLoginWithEmptyBodyReturns400(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
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
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{invalid json'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }
}
