<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\ClientService;
use Doctrine\ORM\EntityManagerInterface;
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
        $this->mockRepository = Mockery::mock(ClientRepository::class)
            ->shouldAllowMockingProtectedMethods();
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

    /**
     * Test updateClientCalledBackStatus throws exception when client not found
     */
    public function testUpdateClientCalledBackStatusThrowsExceptionWhenClientNotFound(): void
    {
        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Client not found');

        $this->clientService->updateClientCalledBackStatus(999, true);
    }

    /**
     * Test updateClientCalledBackStatus successfully updates status
     */
    public function testUpdateClientCalledBackStatusSuccessfullyUpdatesStatus(): void
    {
        $client = Mockery::mock(Client::class);
        $entityManager = Mockery::mock(EntityManagerInterface::class);

        $client->shouldReceive('setIsClientCalledBack')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($client);

        $this->mockRepository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $this->clientService->updateClientCalledBackStatus(1, true);
    }

    /**
     * Test updateClientCalledBackStatus can set status to false
     */
    public function testUpdateClientCalledBackStatusCanSetStatusToFalse(): void
    {
        $client = Mockery::mock(Client::class);
        $entityManager = Mockery::mock(EntityManagerInterface::class);

        $client->shouldReceive('setIsClientCalledBack')
            ->once()
            ->with(false)
            ->andReturnSelf();

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with(5)
            ->andReturn($client);

        $this->mockRepository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $this->clientService->updateClientCalledBackStatus(5, false);
    }

    /**
     * Test updateClient throws exception when client not found
     */
    public function testUpdateClientThrowsExceptionWhenClientNotFound(): void
    {
        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Client not found');

        $this->clientService->updateClient(
            999,
            'John',
            'Doe',
            'john.doe@example.com',
            '0612345678'
        );
    }

    /**
     * Test updateClient successfully updates client information
     */
    public function testUpdateClientSuccessfullyUpdatesClientInformation(): void
    {
        $client = Mockery::mock(Client::class);
        $entityManager = Mockery::mock(EntityManagerInterface::class);

        $client->shouldReceive('setFirstName')
            ->once()
            ->with('John')
            ->andReturnSelf();

        $client->shouldReceive('setLastName')
            ->once()
            ->with('Doe')
            ->andReturnSelf();

        $client->shouldReceive('setEmail')
            ->once()
            ->with('john.doe@example.com')
            ->andReturnSelf();

        $client->shouldReceive('setPhoneNumber')
            ->once()
            ->with('0612345678')
            ->andReturnSelf();

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($client);

        $this->mockRepository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $this->clientService->updateClient(
            1,
            'John',
            'Doe',
            'john.doe@example.com',
            '0612345678'
        );
    }

    /**
     * Test updateClient handles nullable email
     */
    public function testUpdateClientHandlesNullableEmail(): void
    {
        $client = Mockery::mock(Client::class);
        $entityManager = Mockery::mock(EntityManagerInterface::class);

        $client->shouldReceive('setFirstName')
            ->once()
            ->with('Jane')
            ->andReturnSelf();

        $client->shouldReceive('setLastName')
            ->once()
            ->with('Smith')
            ->andReturnSelf();

        $client->shouldReceive('setEmail')
            ->once()
            ->with(null)
            ->andReturnSelf();

        $client->shouldReceive('setPhoneNumber')
            ->once()
            ->with('0698765432')
            ->andReturnSelf();

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with(2)
            ->andReturn($client);

        $this->mockRepository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $this->clientService->updateClient(
            2,
            'Jane',
            'Smith',
            null,
            '0698765432'
        );
    }

    /**
     * Test updateClient uses fluent interface correctly
     */
    public function testUpdateClientUsesFluentInterface(): void
    {
        $client = Mockery::mock(Client::class);
        $entityManager = Mockery::mock(EntityManagerInterface::class);

        // Verify fluent interface by chaining returns
        $client->shouldReceive('setFirstName')
            ->once()
            ->with('Alice')
            ->andReturn($client);

        $client->shouldReceive('setLastName')
            ->once()
            ->with('Brown')
            ->andReturn($client);

        $client->shouldReceive('setEmail')
            ->once()
            ->with('alice@example.com')
            ->andReturn($client);

        $client->shouldReceive('setPhoneNumber')
            ->once()
            ->with('0611223344')
            ->andReturn($client);

        $this->mockRepository
            ->shouldReceive('find')
            ->once()
            ->with(3)
            ->andReturn($client);

        $this->mockRepository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $this->clientService->updateClient(
            3,
            'Alice',
            'Brown',
            'alice@example.com',
            '0611223344'
        );
    }
}
