<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Manufacturer;
use PHPUnit\Framework\TestCase;

class ManufacturerTest extends TestCase
{
    private Manufacturer $manufacturer;

    protected function setUp(): void
    {
        $this->manufacturer = new Manufacturer();
    }

    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $this->assertNull($this->manufacturer->getId());
    }

    public function testSetAndGetName(): void
    {
        $name = 'Renault';
        $this->manufacturer->setName($name);
        $this->assertSame($name, $this->manufacturer->getName());
    }

    public function testFluentInterface(): void
    {
        $result = $this->manufacturer->setName('Peugeot');
        $this->assertSame($this->manufacturer, $result);
    }

    public function testGetCarsReturnsEmptyCollectionByDefault(): void
    {
        $cars = $this->manufacturer->getCars();
        $this->assertCount(0, $cars);
    }

    public function testAddCar(): void
    {
        $car = $this->createMock(\App\Entity\Car::class);
        $car->expects($this->once())
            ->method('setManufacturer')
            ->with($this->manufacturer);

        $this->manufacturer->addCar($car);

        $this->assertCount(1, $this->manufacturer->getCars());
        $this->assertTrue($this->manufacturer->getCars()->contains($car));
    }

    public function testAddCarDoesNotDuplicate(): void
    {
        $car = $this->createMock(\App\Entity\Car::class);
        $car->expects($this->once())
            ->method('setManufacturer');

        $this->manufacturer->addCar($car);
        $this->manufacturer->addCar($car);

        $this->assertCount(1, $this->manufacturer->getCars());
    }

    public function testRemoveCar(): void
    {
        $car = $this->createMock(\App\Entity\Car::class);
        $car->method('setManufacturer');

        $this->manufacturer->addCar($car);
        $this->assertCount(1, $this->manufacturer->getCars());

        $car->expects($this->once())
            ->method('getManufacturer')
            ->willReturn($this->manufacturer);
        $car->expects($this->once())
            ->method('setManufacturer')
            ->with(null);

        $this->manufacturer->removeCar($car);
        $this->assertCount(0, $this->manufacturer->getCars());
    }
}
