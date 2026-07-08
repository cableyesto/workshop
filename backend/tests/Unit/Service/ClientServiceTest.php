<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\ClientRepository;
use App\Service\ClientService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use TypeError;

class ClientServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ClientRepository $mockRepository;
    private ClientService $clientService;

    protected function setUp(): void
    {
        $this->mockRepository = Mockery::mock(ClientRepository::class);
        $this->clientService = new ClientService($this->mockRepository);
    }

    /**
     * Test that updateClientCalledBackStatus throws TypeError when string is passed instead of bool
     *
     * This test verifies that PHP's strict_types enforcement is working correctly.
     * When a string is passed to a bool parameter, PHP should throw a TypeError.
     *
     * @return void
     */
    public function testUpdateClientCalledBackStatusThrowsTypeErrorWhenStringGiven(): void
    {
        // Arrange: Expect TypeError to be thrown
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('bool');

        // Act: Call service method with wrong type (string instead of bool)
        // @phpstan-ignore-next-line - Intentionally passing wrong type to test strict_types
        $this->clientService->updateClientCalledBackStatus(1, "true");

        // Assert: Exception is thrown automatically (no explicit assertion needed)
    }
}
