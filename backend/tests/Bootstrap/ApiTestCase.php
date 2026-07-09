<?php

declare(strict_types=1);

namespace App\Tests\Bootstrap;

use App\Tests\Fixtures\SecurityFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base test class for all API integration tests
 *
 * Provides:
 * - JWT token generation
 * - Authenticated client creation
 * - JSON assertion helpers
 */
abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        // Load fixtures before each test to ensure clean state
        $this->loadFixtures();
    }

    /**
     * Load test fixtures programmatically
     * This allows us to keep fixtures in tests/ directory
     */
    protected function loadFixtures(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Create fixture loader
        $loader = new Loader();

        // Add security fixtures (users, garages)
        $securityFixtures = new SecurityFixtures();
        $loader->addFixture($securityFixtures);

        // Add reference data fixtures (colors, manufacturers, service tasks)
        $referenceDataFixtures = new \App\Tests\Fixtures\ReferenceDataFixtures();
        $loader->addFixture($referenceDataFixtures);

        // Purge and load fixtures
        $purger = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    /**
     * Get JWT token by authenticating with email and password
     *
     * @param string $email User email
     * @param string $password User password
     * @param string|null $siret Optional SIRET for receptionist login (sent in body)
     * @return string JWT token
     */
    protected function getJwtToken(string $email, string $password, ?string $siret = null): string
    {
        $payload = [
            'email' => $email,
            'password' => $password,
        ];

        // Add SIRET to body for Receptionist login
        if ($siret !== null) {
            $payload['siret'] = $siret;
        }

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);

        return $data['token'];
    }

    /**
     * Get JWT token for mechanic by authenticating with PIN
     *
     * @param string $siret Garage SIRET
     * @param string $pin Mechanic PIN (4 digits)
     * @return string JWT token
     */
    protected function getMechanicJwtToken(string $siret, string $pin): string
    {
        $this->client->request(
            'POST',
            "/api/garage/{$siret}/mechanic/login",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'pin' => $pin,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);

        return $data['token'];
    }

    /**
     * Create an authenticated HTTP client with JWT token
     *
     * @param string $token JWT token
     * @return KernelBrowser Authenticated client
     */
    protected function createAuthenticatedClient(string $token): KernelBrowser
    {
        $client = static::createClient();
        $client->setServerParameter('HTTP_AUTHORIZATION', "Bearer {$token}");

        return $client;
    }

    /**
     * Assert that response is JSON with expected status code
     *
     * @param Response $response HTTP response
     * @param int $expectedStatusCode Expected status code
     */
    protected function assertJsonResponse(Response $response, int $expectedStatusCode): void
    {
        $this->assertEquals(
            $expectedStatusCode,
            $response->getStatusCode(),
            sprintf(
                'Expected status code %d, got %d. Response: %s',
                $expectedStatusCode,
                $response->getStatusCode(),
                $response->getContent()
            )
        );

        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            'Response is not JSON'
        );
    }

    /**
     * Assert that JSON response has expected structure
     *
     * @param array $expectedStructure Expected keys in response
     * @param array $actualData Actual response data
     */
    protected function assertJsonStructure(array $expectedStructure, array $actualData): void
    {
        foreach ($expectedStructure as $key) {
            $this->assertArrayHasKey(
                $key,
                $actualData,
                "Expected key '{$key}' not found in JSON response"
            );
        }
    }

    /**
     * Decode JSON response content
     *
     * @param Response $response HTTP response
     * @return array Decoded JSON data
     */
    protected function getJsonResponse(Response $response): array
    {
        $content = $response->getContent();
        $this->assertJson($content);

        return json_decode($content, true);
    }

    /**
     * Assert validation error in response
     *
     * @param Response $response HTTP response
     * @param string|null $expectedField Expected field with error (optional)
     */
    protected function assertValidationError(Response $response, ?string $expectedField = null): void
    {
        $this->assertEquals(400, $response->getStatusCode());

        $data = $this->getJsonResponse($response);

        if ($expectedField !== null) {
            $this->assertArrayHasKey('errors', $data);
            $this->assertArrayHasKey($expectedField, $data['errors']);
        }
    }

    /**
     * Create an expired JWT token for testing
     *
     * @return string Expired JWT token
     */
    protected function createExpiredToken(): string
    {
        // This is a manually crafted expired token
        // In real implementation, you might need to generate one with past exp claim
        // For now, we'll use a token that expired in the past
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'email' => 'test@example.com',
            'exp' => time() - 3600, // Expired 1 hour ago
            'iat' => time() - 7200,
        ]));
        $signature = 'invalid_signature';

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Create a JWT token with invalid signature
     *
     * @return string JWT token with invalid signature
     */
    protected function createInvalidSignatureToken(): string
    {
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'email' => 'test@example.com',
            'exp' => time() + 3600,
            'iat' => time(),
        ]));
        $signature = 'tampered_signature_12345';

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Create a malformed JWT token (not 3 parts)
     *
     * @return string Malformed JWT token
     */
    protected function createMalformedToken(): string
    {
        return 'invalid.token';
    }
}
