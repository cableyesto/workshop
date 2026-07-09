<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Car;
use App\Entity\Client;
use App\Entity\Color;
use App\Entity\Intervention;
use App\Entity\Manufacturer;
use PHPUnit\Framework\TestCase;

class CarTest extends TestCase
{
    private Car $car;

    protected function setUp(): void
    {
        $this->car = new Car();
    }

    public function testSetAndGetModel(): void
    {
        $model = 'Clio';
        $this->car->setModel($model);
        $this->assertSame($model, $this->car->getModel());
    }

    public function testSetAndGetLicensePlate(): void
    {
        $plate = 'AB-123-CD';
        $this->car->setLicensePlate($plate);
        $this->assertSame($plate, $this->car->getLicensePlate());
    }

    public function testSetAndGetRegistrationYear(): void
    {
        $year = 2020;
        $this->car->setRegistrationYear($year);
        $this->assertSame($year, $this->car->getRegistrationYear());
    }

    public function testSetAndGetRegistrationYearAcceptsNull(): void
    {
        $this->car->setRegistrationYear(null);
        $this->assertNull($this->car->getRegistrationYear());
    }

    public function testSetAndGetRegistrationMonth(): void
    {
        $month = 6;
        $this->car->setRegistrationMonth($month);
        $this->assertSame($month, $this->car->getRegistrationMonth());
    }

    public function testSetAndGetRegistrationMonthAcceptsNull(): void
    {
        $this->car->setRegistrationMonth(null);
        $this->assertNull($this->car->getRegistrationMonth());
    }

    public function testSetAndGetMileage(): void
    {
        $mileage = 50000;
        $this->car->setMileage($mileage);
        $this->assertSame($mileage, $this->car->getMileage());
    }

    public function testSetAndGetMileageAcceptsNull(): void
    {
        $this->car->setMileage(null);
        $this->assertNull($this->car->getMileage());
    }

    public function testIsStoredDefaultsToNull(): void
    {
        $this->assertNull($this->car->isStored());
    }

    public function testSetAndIsStored(): void
    {
        $this->car->setIsStored(true);
        $this->assertTrue($this->car->isStored());

        $this->car->setIsStored(false);
        $this->assertFalse($this->car->isStored());
    }

    public function testSetAndGetManufacturer(): void
    {
        $manufacturer = $this->createStub(Manufacturer::class);
        $this->car->setManufacturer($manufacturer);
        $this->assertSame($manufacturer, $this->car->getManufacturer());
    }

    public function testSetAndGetColor(): void
    {
        $color = $this->createStub(Color::class);
        $this->car->setColor($color);
        $this->assertSame($color, $this->car->getColor());
    }

    public function testSetAndGetClient(): void
    {
        $client = $this->createStub(Client::class);
        $this->car->setClient($client);
        $this->assertSame($client, $this->car->getClient());
    }

    public function testGetInterventionsReturnsEmptyCollectionByDefault(): void
    {
        $interventions = $this->car->getInterventions();
        $this->assertCount(0, $interventions);
    }

    public function testAddIntervention(): void
    {
        $intervention = $this->createMock(Intervention::class);
        $intervention->expects($this->once())
            ->method('setCar')
            ->with($this->car);

        $this->car->addIntervention($intervention);

        $this->assertCount(1, $this->car->getInterventions());
        $this->assertTrue($this->car->getInterventions()->contains($intervention));
    }

    public function testAddInterventionDoesNotDuplicates(): void
    {
        $intervention = $this->createMock(Intervention::class);
        $intervention->expects($this->once())
            ->method('setCar');

        $this->car->addIntervention($intervention);
        $this->car->addIntervention($intervention);

        $this->assertCount(1, $this->car->getInterventions());
    }

    public function testRemoveIntervention(): void
    {
        $intervention = $this->createMock(Intervention::class);
        $intervention->method('setCar');

        $this->car->addIntervention($intervention);
        $this->assertCount(1, $this->car->getInterventions());

        $intervention->expects($this->once())
            ->method('getCar')
            ->willReturn($this->car);
        $intervention->expects($this->once())
            ->method('setCar')
            ->with(null);

        $this->car->removeIntervention($intervention);
        $this->assertCount(0, $this->car->getInterventions());
    }

    public function testFluentInterface(): void
    {
        $result = $this->car
            ->setModel('Clio')
            ->setLicensePlate('AB-123-CD')
            ->setIsStored(true);

        $this->assertSame($this->car, $result);
    }
}
