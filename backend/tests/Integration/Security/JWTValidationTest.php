<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Traits\AuthenticationTrait;

/**
 * Integration tests for JWT token validation
 *
 * Verifies that the API properly validates JWT tokens and rejects:
 * - Expired tokens
 * - Malformed tokens
 * - Invalid signatures
 * - Missing tokens
 * - Invalid Bearer format
 * - Corrupted payloads
 */
class JWTValidationTest extends ApiTestCase
{
    use AuthenticationTrait;

    private const PROTECTED_ENDPOINT = '/api/colors';

    /**
     * Test that expired JWT token is rejected
     */
    public function testExpiredTokenReturns401(): void
    {
        $expiredToken = $this->createExpiredToken();

        $this->client->request(
            'GET',
            self::PROTECTED_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$expiredToken}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());

        $data = $this->getJsonResponse($response);
        $this->assertArrayHasKey('message', $data);
        // API returns generic "Invalid JWT Token" for expired tokens
        $this->assertStringContainsString('Invalid', $data['message']);
    }

    /**
     * Test that malformed JWT token (not 3 parts) is rejected
     */
    public function testMalformedTokenReturns401(): void
    {
        $malformedToken = $this->createMalformedToken();

        $this->client->request(
            'GET',
            self::PROTECTED_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$malformedToken}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test that token with invalid signature is rejected
     */
    public function testInvalidSignatureReturns401(): void
    {
        $tamperedToken = $this->createInvalidSignatureToken();

        $this->client->request(
            'GET',
            self::PROTECTED_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$tamperedToken}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());

        $data = $this->getJsonResponse($response);
        $this->assertArrayHasKey('message', $data);
    }

    /**
     * Test that request without Authorization header is rejected
     */
    public function testMissingTokenReturns401(): void
    {
        $this->client->request(
            'GET',
            self::PROTECTED_ENDPOINT,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());

        $data = $this->getJsonResponse($response);
        $this->assertArrayHasKey('message', $data);
    }

    /**
     * Test that token without "Bearer " prefix is rejected
     */
    public function testInvalidBearerFormatReturns401(): void
    {
        $validToken = $this->getOwnerToken();

        // Send token without "Bearer " prefix
        $this->client->request(
            'GET',
            self::PROTECTED_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => $validToken, // Missing "Bearer "
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test that token with corrupted payload is rejected
     */
    public function testTokenWithInvalidPayloadReturns401(): void
    {
        $corruptedToken = $this->createCorruptedPayloadToken();

        $this->client->request(
            'GET',
            self::PROTECTED_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$corruptedToken}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test that valid format token with different signing key is rejected
     */
    public function testTokenFromDifferentKeyReturns401(): void
    {
        // Create a token that looks valid but signed with different key
        $differentKeyToken = $this->createTokenWithDifferentKey();

        $this->client->request(
            'GET',
            self::PROTECTED_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$differentKeyToken}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test that valid token still works (sanity check)
     */
    public function testValidTokenReturns200(): void
    {
        $validToken = $this->getOwnerToken();

        $this->client->request(
            'GET',
            self::PROTECTED_ENDPOINT,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$validToken}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
    }

    /**
     * Create a token with corrupted base64 payload
     */
    private function createCorruptedPayloadToken(): string
    {
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $corruptedPayload = 'corrupted!!!not-valid-base64@@@';
        $signature = 'invalid_signature';

        return "{$header}.{$corruptedPayload}.{$signature}";
    }

    /**
     * Create a token that appears valid but signed with wrong key
     * Note: This will be rejected by signature verification
     */
    private function createTokenWithDifferentKey(): string
    {
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'username' => 'test@example.com',
            'roles' => ['ROLE_USER'],
            'exp' => time() + 3600,
            'iat' => time(),
        ]));
        // Wrong signature (would need different private key)
        $signature = base64_encode('wrong_signature_from_different_key_12345678901234567890');

        return "{$header}.{$payload}.{$signature}";
    }
}
