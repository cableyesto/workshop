<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Color;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    private Color $color;

    protected function setUp(): void
    {
        $this->color = new Color();
    }

    public function testSetAndGetName(): void
    {
        $name = 'Bleu';
        $this->color->setName($name);
        $this->assertSame($name, $this->color->getName());
    }

    public function testFluentInterface(): void
    {
        $result = $this->color->setName('Rouge');
        $this->assertSame($this->color, $result);
    }

    public function testSetAndGetHexCode(): void
    {
        $hexCode = '#FF0000';
        $this->color->setHexCode($hexCode);
        $this->assertSame($hexCode, $this->color->getHexCode());
    }

    public function testGetCarsReturnsEmptyCollectionByDefault(): void
    {
        $cars = $this->color->getCars();
        $this->assertCount(0, $cars);
    }

    public function testAddCar(): void
    {
        $car = $this->createMock(\App\Entity\Car::class);
        $car->expects($this->once())
            ->method('setColor')
            ->with($this->color);

        $this->color->addCar($car);

        $this->assertCount(1, $this->color->getCars());
        $this->assertTrue($this->color->getCars()->contains($car));
    }

    public function testAddCarDoesNotDuplicate(): void
    {
        $car = $this->createMock(\App\Entity\Car::class);
        $car->expects($this->once())
            ->method('setColor');

        $this->color->addCar($car);
        $this->color->addCar($car);

        $this->assertCount(1, $this->color->getCars());
    }

    public function testRemoveCar(): void
    {
        $car = $this->createMock(\App\Entity\Car::class);
        $car->method('setColor');

        $this->color->addCar($car);
        $this->assertCount(1, $this->color->getCars());

        $car->expects($this->once())
            ->method('getColor')
            ->willReturn($this->color);
        $car->expects($this->once())
            ->method('setColor')
            ->with(null);

        $this->color->removeCar($car);
        $this->assertCount(0, $this->color->getCars());
    }
}
