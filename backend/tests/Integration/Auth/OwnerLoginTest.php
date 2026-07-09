<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Fixtures\SecurityFixtures;
use App\Tests\Traits\AuthenticationTrait;

/**
 * Integration tests for Owner login flow
 *
 * Tests /api/login endpoint for Owner authentication
 */
class OwnerLoginTest extends ApiTestCase
{
    use AuthenticationTrait;

    /**
     * Test successful login with valid credentials
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
                'email' => SecurityFixtures::OWNER_EMAIL,
                'password' => SecurityFixtures::OWNER_PASSWORD,
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
                'password' => SecurityFixtures::OWNER_PASSWORD,
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
                'email' => SecurityFixtures::OWNER_EMAIL,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test login fails when email format is invalid
     * Note: Returns 401 (not 400) because authentication is attempted before email validation
     */
    public function testLoginWithInvalidEmailFormatReturns401(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'not-an-email',
                'password' => SecurityFixtures::OWNER_PASSWORD,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
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
                'password' => SecurityFixtures::OWNER_PASSWORD,
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
                'email' => SecurityFixtures::OWNER_EMAIL,
                'password' => 'wrongpassword',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test that JWT token contains correct roles
     */
    public function testTokenContainsOwnerRole(): void
    {
        $token = $this->getOwnerToken();

        // Decode JWT payload (without verification for testing purposes)
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'JWT should have 3 parts');

        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('roles', $payload);
        $this->assertContains('ROLE_OWNER', $payload['roles']);
    }

    /**
     * Test that JWT token contains garage IDs
     */
    public function testTokenContainsGarageIds(): void
    {
        $token = $this->getOwnerToken();

        // Decode JWT payload
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('garage_ids', $payload);
        $this->assertIsArray($payload['garage_ids']);
        $this->assertNotEmpty($payload['garage_ids']);
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

    /**
     * Test that token contains user identifier (username)
     */
    public function testTokenContainsUserIdentifier(): void
    {
        $token = $this->getOwnerToken();

        // Decode JWT payload
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);

        // JWT uses 'username' field for user identifier (not 'email')
        $this->assertArrayHasKey('username', $payload);
        $this->assertEquals(SecurityFixtures::OWNER_EMAIL, $payload['username']);
    }
}
