<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Tests\Bootstrap\ApiTestCase;
use App\Tests\Traits\AuthenticationTrait;

/**
 * Integration tests for ColorController
 *
 * Tests basic CRUD operations for color reference data.
 */
class ColorControllerTest extends ApiTestCase
{
    use AuthenticationTrait;

    /**
     * Test that getting all colors returns 200 OK
     */
    public function testGetAllColorsReturns200(): void
    {
        $token = $this->getOwnerToken();

        $this->client->request(
            'GET',
            '/api/colors',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Should be able to retrieve colors list');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data, 'Colors response should be an array');
    }

    /**
     * Test that getting a specific color by ID returns 200 OK
     */
    public function testGetColorByIdReturns200(): void
    {
        $token = $this->getOwnerToken();

        // First, get all colors to find a valid ID
        $this->client->request(
            'GET',
            '/api/colors',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $colors = $this->getJsonResponse($this->client->getResponse());

        if (empty($colors)) {
            $this->markTestSkipped('No colors available in database');
        }

        $colorId = $colors[0]['id'];

        // Now get specific color
        $this->client->request(
            'GET',
            "/api/colors/{$colorId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Should be able to retrieve a specific color');

        $data = $this->getJsonResponse($response);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($colorId, $data['id']);
    }

    /**
     * Test that requesting a non-existent color returns 404
     */
    public function testGetNonExistentColorReturns404(): void
    {
        $token = $this->getOwnerToken();

        $this->client->request(
            'GET',
            '/api/colors/99999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode(), 'Non-existent color should return 404');
    }

    /**
     * Test that searching colors returns matching results
     */
    public function testSearchColorsReturns200(): void
    {
        $token = $this->getOwnerToken();

        $this->client->request(
            'GET',
            '/api/colors/search?q=Blanc',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => "Bearer {$token}",
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful('Should be able to search colors');

        $data = $this->getJsonResponse($response);
        $this->assertIsArray($data, 'Search results should be an array');
    }

    /**
     * Test that colors endpoint requires authentication
     */
    public function testColorsRequireAuthentication(): void
    {
        // Request without token
        $this->client->request(
            'GET',
            '/api/colors',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode(), 'Colors endpoint should require authentication');
    }
}
