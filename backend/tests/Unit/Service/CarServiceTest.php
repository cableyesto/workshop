<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Car;
use App\Entity\Client;
use App\Entity\Color;
use App\Entity\Intervention;
use App\Entity\Manufacturer;
use App\Enum\InterventionStatus;
use App\Repository\CarRepository;
use App\Repository\ClientRepository;
use App\Repository\ColorRepository;
use App\Repository\GarageRepository;
use App\Repository\ManufacturerRepository;
use App\Service\CarService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CarServiceTest extends TestCase
{
    private CarRepository $carRepository;
    private CarService $carService;

    protected function setUp(): void
    {
        $this->carRepository = $this->createMock(CarRepository::class);
        $colorRepository = $this->createMock(ColorRepository::class);
        $clientRepository = $this->createMock(ClientRepository::class);
        $garageRepository = $this->createMock(GarageRepository::class);
        $manufacturerRepository = $this->createMock(ManufacturerRepository::class);

        $this->carService = new CarService(
            $this->carRepository,
            $colorRepository,
            $clientRepository,
            $garageRepository,
            $manufacturerRepository
        );
    }

    /**
     * Test getAllCars returns empty array when no cars
     */
    public function testGetAllCarsReturnsEmptyArrayWhenNoCars(): void
    {
        $this->carRepository
            ->expects($this->once())
            ->method('findAllForGarage')
            ->with(1)
            ->willReturn([]);

        $result = $this->carService->getAllCars(1);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Test getAllCars returns formatted cars with client data
     */
    public function testGetAllCarsReturnsFormattedCarsWithClientData(): void
    {
        $car = $this->createCarStub(1, 'Renault', 'Clio', 'AB-123-CD', 'Bleu', 2020, 5, 50000, false);

        $this->carRepository
            ->expects($this->once())
            ->method('findAllForGarage')
            ->with(1)
            ->willReturn([$car]);

        $result = $this->carService->getAllCars(1);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Renault', $result[0]['manufacturer']);
        $this->assertEquals('Clio', $result[0]['model']);
        $this->assertEquals('AB-123-CD', $result[0]['licensePlate']);
        $this->assertEquals('Bleu', $result[0]['color']);
        $this->assertEquals(2020, $result[0]['registrationYear']);
        $this->assertEquals(5, $result[0]['registrationMonth']);
        $this->assertEquals(50000, $result[0]['mileage']);
        $this->assertFalse($result[0]['isStored']);
        $this->assertArrayHasKey('client', $result[0]);
    }

    /**
     * Test getStoredCars returns only stored cars
     */
    public function testGetStoredCarsReturnsOnlyStoredCars(): void
    {
        $car = $this->createCarStub(1, 'Peugeot', '208', 'EF-456-GH', 'Rouge', 2021, 3, 30000, true);

        $this->carRepository
            ->expects($this->once())
            ->method('findStoredCars')
            ->with(1)
            ->willReturn([$car]);

        $result = $this->carService->getStoredCars(1);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Peugeot', $result[0]['manufacturer']);
    }

    /**
     * Test getStoredCars returns empty array when no stored cars
     */
    public function testGetStoredCarsReturnsEmptyArrayWhenNoStoredCars(): void
    {
        $this->carRepository
            ->expects($this->once())
            ->method('findStoredCars')
            ->with(1)
            ->willReturn([]);

        $result = $this->carService->getStoredCars(1);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Test getCarsWithInterventions calculates status Active for InProgress
     */
    public function testGetCarsWithInterventionsCalculatesStatusActive(): void
    {
        $intervention = $this->createStub(Intervention::class);
        $intervention->method('getStatus')->willReturn(InterventionStatus::InProgress);
        $interventions = new ArrayCollection([$intervention]);

        $car = $this->createCarStubWithInterventions(1, 'BMW', 'X5', 'MN-012-OP', 'Blanc', 2022, 1, 10000, false, $interventions);

        $this->carRepository
            ->expects($this->once())
            ->method('findCarsWithInterventions')
            ->with(1)
            ->willReturn([$car]);

        $result = $this->carService->getCarsWithInterventions(1);

        $this->assertEquals('Active', $result[0]['intervention']['globalStatus']);
    }

    /**
     * Test calculateGlobalStatus returns Paused
     */
    public function testCalculatesStatusPaused(): void
    {
        $intervention = $this->createStub(Intervention::class);
        $intervention->method('getStatus')->willReturn(InterventionStatus::Paused);
        $interventions = new ArrayCollection([$intervention]);

        $car = $this->createCarStubWithInterventions(1, 'Audi', 'A4', 'QR-345-ST', 'Gris', 2020, 8, 40000, false, $interventions);

        $this->carRepository
            ->expects($this->once())
            ->method('findCarsWithInterventions')
            ->with(1)
            ->willReturn([$car]);

        $result = $this->carService->getCarsWithInterventions(1);

        $this->assertEquals('Paused', $result[0]['intervention']['globalStatus']);
    }

    /**
     * Test calculateGlobalStatus returns Assigned
     */
    public function testCalculatesStatusAssigned(): void
    {
        $intervention = $this->createStub(Intervention::class);
        $intervention->method('getStatus')->willReturn(InterventionStatus::Assigned);
        $interventions = new ArrayCollection([$intervention]);

        $car = $this->createCarStubWithInterventions(1, 'Mercedes', 'C220', 'UV-678-WX', 'Noir', 2021, 12, 25000, false, $interventions);

        $this->carRepository
            ->expects($this->once())
            ->method('findCarsWithInterventions')
            ->with(1)
            ->willReturn([$car]);

        $result = $this->carService->getCarsWithInterventions(1);

        $this->assertEquals('Assigned', $result[0]['intervention']['globalStatus']);
    }

    /**
     * Test calculateGlobalStatus returns Completed when all interventions stopped
     */
    public function testCalculatesStatusCompleted(): void
    {
        $intervention = $this->createStub(Intervention::class);
        $intervention->method('getStatus')->willReturn(InterventionStatus::Stopped);
        $interventions = new ArrayCollection([$intervention]);

        $car = $this->createCarStubWithInterventions(1, 'Toyota', 'Corolla', 'YZ-901-AB', 'Argent', 2018, 4, 90000, false, $interventions);

        $this->carRepository
            ->expects($this->once())
            ->method('findCarsWithInterventions')
            ->with(1)
            ->willReturn([$car]);

        $result = $this->carService->getCarsWithInterventions(1);

        $this->assertEquals('Completed', $result[0]['intervention']['globalStatus']);
    }

    /**
     * Test calculateGlobalStatus priority: InProgress > Paused > Assigned
     */
    public function testCalculatesStatusWithMultipleInterventionsReturnsHighestPriority(): void
    {
        $intervention1 = $this->createStub(Intervention::class);
        $intervention1->method('getStatus')->willReturn(InterventionStatus::Stopped);

        $intervention2 = $this->createStub(Intervention::class);
        $intervention2->method('getStatus')->willReturn(InterventionStatus::InProgress);

        $intervention3 = $this->createStub(Intervention::class);
        $intervention3->method('getStatus')->willReturn(InterventionStatus::Paused);

        $interventions = new ArrayCollection([$intervention1, $intervention2, $intervention3]);

        $car = $this->createCarStubWithInterventions(1, 'Nissan', 'Qashqai', 'CD-234-EF', 'Bleu', 2020, 7, 35000, false, $interventions);

        $this->carRepository
            ->expects($this->once())
            ->method('findCarsWithInterventions')
            ->with(1)
            ->willReturn([$car]);

        $result = $this->carService->getCarsWithInterventions(1);

        // InProgress has highest priority
        $this->assertEquals('Active', $result[0]['intervention']['globalStatus']);
    }

    /**
     * Test getCarsForRestitution returns only completed cars
     */
    public function testGetCarsForRestitutionReturnsOnlyCompletedCars(): void
    {
        $intervention = $this->createStub(Intervention::class);
        $intervention->method('getStatus')->willReturn(InterventionStatus::Stopped);
        $interventions = new ArrayCollection([$intervention]);

        $car = $this->createCarStubWithInterventions(1, 'Volkswagen', 'Golf', 'GH-567-IJ', 'Vert', 2019, 9, 60000, false, $interventions);

        $this->carRepository
            ->expects($this->once())
            ->method('findCarsForRestitution')
            ->with(1)
            ->willReturn([$car]);

        $result = $this->carService->getCarsForRestitution(1);

        $this->assertCount(1, $result);
        $this->assertEquals('Completed', $result[0]['intervention']['globalStatus']);
    }

    /**
     * Test getCarsForRestitution filters out non-completed cars
     */
    public function testGetCarsForRestitutionFiltersOutNonCompletedCars(): void
    {
        $intervention1 = $this->createStub(Intervention::class);
        $intervention1->method('getStatus')->willReturn(InterventionStatus::InProgress);
        $interventions1 = new ArrayCollection([$intervention1]);
        $car1 = $this->createCarStubWithInterventions(1, 'Seat', 'Ibiza', 'KL-890-MN', 'Rouge', 2020, 2, 45000, false, $interventions1);

        $intervention2 = $this->createStub(Intervention::class);
        $intervention2->method('getStatus')->willReturn(InterventionStatus::Stopped);
        $interventions2 = new ArrayCollection([$intervention2]);
        $car2 = $this->createCarStubWithInterventions(2, 'Skoda', 'Octavia', 'OP-123-QR', 'Bleu', 2021, 5, 20000, false, $interventions2);

        $this->carRepository
            ->expects($this->once())
            ->method('findCarsForRestitution')
            ->with(1)
            ->willReturn([$car1, $car2]);

        $result = $this->carService->getCarsForRestitution(1);

        // Only car2 should be returned (Completed status)
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]['id']);
    }

    /**
     * Helper to create a basic car stub
     */
    private function createCarStub(
        int $id,
        string $manufacturerName,
        string $model,
        string $licensePlate,
        string $colorName,
        int $registrationYear,
        int $registrationMonth,
        int $mileage,
        bool $isStored
    ): Car {
        $manufacturer = $this->createStub(Manufacturer::class);
        $manufacturer->method('getName')->willReturn($manufacturerName);

        $color = $this->createStub(Color::class);
        $color->method('getName')->willReturn($colorName);

        $client = $this->createStub(Client::class);
        $client->method('getId')->willReturn(1);
        $client->method('getFirstName')->willReturn('John');
        $client->method('getLastName')->willReturn('Doe');
        $client->method('getEmail')->willReturn('john@example.com');
        $client->method('getPhoneNumber')->willReturn('0612345678');
        $client->method('isClientCalledBack')->willReturn(false);

        $car = $this->createStub(Car::class);
        $car->method('getId')->willReturn($id);
        $car->method('getManufacturer')->willReturn($manufacturer);
        $car->method('getModel')->willReturn($model);
        $car->method('getLicensePlate')->willReturn($licensePlate);
        $car->method('getColor')->willReturn($color);
        $car->method('getRegistrationYear')->willReturn($registrationYear);
        $car->method('getRegistrationMonth')->willReturn($registrationMonth);
        $car->method('getMileage')->willReturn($mileage);
        $car->method('isStored')->willReturn($isStored);
        $car->method('getClient')->willReturn($client);
        $car->method('getInterventions')->willReturn(new ArrayCollection());

        return $car;
    }

    /**
     * Helper to create a car stub with interventions
     */
    private function createCarStubWithInterventions(
        int $id,
        string $manufacturerName,
        string $model,
        string $licensePlate,
        string $colorName,
        int $registrationYear,
        int $registrationMonth,
        int $mileage,
        bool $isStored,
        ArrayCollection $interventions
    ): Car {
        $manufacturer = $this->createStub(Manufacturer::class);
        $manufacturer->method('getName')->willReturn($manufacturerName);

        $color = $this->createStub(Color::class);
        $color->method('getName')->willReturn($colorName);

        $client = $this->createStub(Client::class);
        $client->method('getId')->willReturn(1);
        $client->method('getFirstName')->willReturn('John');
        $client->method('getLastName')->willReturn('Doe');
        $client->method('getEmail')->willReturn('john@example.com');
        $client->method('getPhoneNumber')->willReturn('0612345678');
        $client->method('isClientCalledBack')->willReturn(false);

        $car = $this->createStub(Car::class);
        $car->method('getId')->willReturn($id);
        $car->method('getManufacturer')->willReturn($manufacturer);
        $car->method('getModel')->willReturn($model);
        $car->method('getLicensePlate')->willReturn($licensePlate);
        $car->method('getColor')->willReturn($color);
        $car->method('getRegistrationYear')->willReturn($registrationYear);
        $car->method('getRegistrationMonth')->willReturn($registrationMonth);
        $car->method('getMileage')->willReturn($mileage);
        $car->method('isStored')->willReturn($isStored);
        $car->method('getClient')->willReturn($client);
        $car->method('getInterventions')->willReturn($interventions);

        return $car;
    }
}
