<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Car;
use App\Entity\Client;
use App\Entity\Garage;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();
    }

    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $this->assertNull($this->client->getId());
    }

    public function testSetAndGetFirstName(): void
    {
        $firstName = 'Jean';
        $this->client->setFirstName($firstName);
        $this->assertSame($firstName, $this->client->getFirstName());
    }

    public function testSetAndGetLastName(): void
    {
        $lastName = 'Dupont';
        $this->client->setLastName($lastName);
        $this->assertSame($lastName, $this->client->getLastName());
    }

    public function testSetAndGetEmail(): void
    {
        $email = 'jean.dupont@example.com';
        $this->client->setEmail($email);
        $this->assertSame($email, $this->client->getEmail());
    }

    public function testEmailCanBeNull(): void
    {
        $this->client->setEmail(null);
        $this->assertNull($this->client->getEmail());
    }

    public function testSetAndGetPhoneNumber(): void
    {
        $phone = '0612345678';
        $this->client->setPhoneNumber($phone);
        $this->assertSame($phone, $this->client->getPhoneNumber());
    }

    public function testIsClientCalledBackDefaultsToNull(): void
    {
        $this->assertNull($this->client->isClientCalledBack());
    }

    public function testSetAndIsClientCalledBack(): void
    {
        $this->client->setIsClientCalledBack(true);
        $this->assertTrue($this->client->isClientCalledBack());

        $this->client->setIsClientCalledBack(false);
        $this->assertFalse($this->client->isClientCalledBack());
    }

    public function testSetAndGetGarage(): void
    {
        $garage = $this->createStub(Garage::class);
        $this->client->setGarage($garage);
        $this->assertSame($garage, $this->client->getGarage());
    }

    public function testGetCarsReturnsEmptyCollectionByDefault(): void
    {
        $cars = $this->client->getCars();
        $this->assertCount(0, $cars);
    }

    public function testAddCar(): void
    {
        $car = $this->createMock(Car::class);
        $car->expects($this->once())
            ->method('setClient')
            ->with($this->client);

        $this->client->addCar($car);

        $this->assertCount(1, $this->client->getCars());
        $this->assertTrue($this->client->getCars()->contains($car));
    }

    public function testAddCarDoesNotDuplicate(): void
    {
        $car = $this->createMock(Car::class);
        $car->expects($this->once())
            ->method('setClient');

        $this->client->addCar($car);
        $this->client->addCar($car);

        $this->assertCount(1, $this->client->getCars());
    }

    public function testRemoveCar(): void
    {
        $car = $this->createMock(Car::class);
        $car->method('setClient');

        $this->client->addCar($car);
        $this->assertCount(1, $this->client->getCars());

        $car->expects($this->once())
            ->method('getClient')
            ->willReturn($this->client);
        $car->expects($this->once())
            ->method('setClient')
            ->with(null);

        $this->client->removeCar($car);
        $this->assertCount(0, $this->client->getCars());
    }

    public function testFluentInterface(): void
    {
        $result = $this->client
            ->setFirstName('Jean')
            ->setLastName('Dupont')
            ->setEmail('jean@example.com')
            ->setPhoneNumber('0612345678')
            ->setIsClientCalledBack(true);

        $this->assertSame($this->client, $result);
    }
}
