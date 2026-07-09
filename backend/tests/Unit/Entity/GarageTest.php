<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Employee;
use App\Entity\Garage;
use App\Entity\Owner;
use App\Entity\TimeSlot;
use PHPUnit\Framework\TestCase;

class GarageTest extends TestCase
{
    private Garage $garage;

    protected function setUp(): void
    {
        $this->garage = new Garage();
    }

    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $this->assertNull($this->garage->getId());
    }

    public function testSetAndGetSiretNumber(): void
    {
        $siret = '12345678901234';
        $this->garage->setSiretNumber($siret);
        $this->assertSame($siret, $this->garage->getSiretNumber());
    }

    public function testSetAndGetName(): void
    {
        $name = 'Garage Central';
        $this->garage->setName($name);
        $this->assertSame($name, $this->garage->getName());
    }

    public function testSetAndGetStreet(): void
    {
        $street = '123 Rue de la Paix';
        $this->garage->setStreet($street);
        $this->assertSame($street, $this->garage->getStreet());
    }

    public function testSetAndGetCity(): void
    {
        $city = 'Paris';
        $this->garage->setCity($city);
        $this->assertSame($city, $this->garage->getCity());
    }

    public function testSetAndGetZipCode(): void
    {
        $zipCode = '75001';
        $this->garage->setZipCode($zipCode);
        $this->assertSame($zipCode, $this->garage->getZipCode());
    }

    public function testSetAndGetPhone(): void
    {
        $phone = '0123456789';
        $this->garage->setPhone($phone);
        $this->assertSame($phone, $this->garage->getPhone());
    }

    public function testPhoneCanBeNull(): void
    {
        $this->garage->setPhone(null);
        $this->assertNull($this->garage->getPhone());
    }

    public function testGetOwnersReturnsEmptyCollectionByDefault(): void
    {
        $owners = $this->garage->getOwners();
        $this->assertCount(0, $owners);
    }

    public function testAddOwner(): void
    {
        $owner = $this->createMock(Owner::class);
        $owner->expects($this->once())
            ->method('addGarage')
            ->with($this->garage);

        $this->garage->addOwner($owner);

        $this->assertCount(1, $this->garage->getOwners());
        $this->assertTrue($this->garage->getOwners()->contains($owner));
    }

    public function testAddOwnerDoesNotDuplicate(): void
    {
        $owner = $this->createMock(Owner::class);
        $owner->expects($this->once())
            ->method('addGarage');

        $this->garage->addOwner($owner);
        $this->garage->addOwner($owner);

        $this->assertCount(1, $this->garage->getOwners());
    }

    public function testGetEmployeesReturnsEmptyCollectionByDefault(): void
    {
        $employees = $this->garage->getEmployees();
        $this->assertCount(0, $employees);
    }

    public function testAddEmployee(): void
    {
        $employee = $this->createStub(Employee::class);

        $this->garage->addEmployee($employee);

        $this->assertCount(1, $this->garage->getEmployees());
        $this->assertTrue($this->garage->getEmployees()->contains($employee));
    }

    public function testAddEmployeeDoesNotDuplicate(): void
    {
        $employee = $this->createStub(Employee::class);

        $this->garage->addEmployee($employee);
        $this->garage->addEmployee($employee);

        $this->assertCount(1, $this->garage->getEmployees());
    }

    public function testGetTimeSlotsReturnsEmptyCollectionByDefault(): void
    {
        $timeSlots = $this->garage->getTimeSlots();
        $this->assertCount(0, $timeSlots);
    }

    public function testAddTimeSlot(): void
    {
        $timeSlot = $this->createMock(TimeSlot::class);
        $timeSlot->expects($this->once())
            ->method('setGarage')
            ->with($this->garage);

        $this->garage->addTimeSlot($timeSlot);

        $this->assertCount(1, $this->garage->getTimeSlots());
        $this->assertTrue($this->garage->getTimeSlots()->contains($timeSlot));
    }

    public function testAddTimeSlotDoesNotDuplicate(): void
    {
        $timeSlot = $this->createMock(TimeSlot::class);
        $timeSlot->expects($this->once())
            ->method('setGarage');

        $this->garage->addTimeSlot($timeSlot);
        $this->garage->addTimeSlot($timeSlot);

        $this->assertCount(1, $this->garage->getTimeSlots());
    }

    public function testGetClientsReturnsEmptyCollectionByDefault(): void
    {
        $clients = $this->garage->getClients();
        $this->assertCount(0, $clients);
    }

    public function testFluentInterface(): void
    {
        $result = $this->garage
            ->setSiretNumber('12345678901234')
            ->setName('Garage Test')
            ->setStreet('123 Rue Test')
            ->setCity('Paris')
            ->setZipCode('75001');

        $this->assertSame($this->garage, $result);
    }

    /**
     * Test removing an owner
     */
    public function testRemoveOwner(): void
    {
        $owner = $this->createMock(Owner::class);
        $owner->expects($this->once())->method('addGarage')->with($this->garage);
        $owner->expects($this->once())->method('removeGarage')->with($this->garage);

        $this->garage->addOwner($owner);
        $this->assertCount(1, $this->garage->getOwners());

        $this->garage->removeOwner($owner);
        $this->assertCount(0, $this->garage->getOwners());
    }

    /**
     * Test removing a time slot
     */
    public function testRemoveTimeSlot(): void
    {
        $timeSlot = $this->createMock(TimeSlot::class);
        // setGarage is called twice: once with $this->garage, once with null
        $timeSlot->expects($this->exactly(2))->method('setGarage');
        $timeSlot->expects($this->once())->method('getGarage')->willReturn($this->garage);

        $this->garage->addTimeSlot($timeSlot);
        $this->assertCount(1, $this->garage->getTimeSlots());

        $this->garage->removeTimeSlot($timeSlot);
        $this->assertCount(0, $this->garage->getTimeSlots());
    }

    /**
     * Test removing an employee
     */
    public function testRemoveEmployee(): void
    {
        $employee = $this->createStub(Employee::class);

        $this->garage->addEmployee($employee);
        $this->assertCount(1, $this->garage->getEmployees());

        $this->garage->removeEmployee($employee);
        $this->assertCount(0, $this->garage->getEmployees());
    }

    /**
     * Test adding a client
     */
    public function testAddClient(): void
    {
        $client = $this->createMock(\App\Entity\Client::class);
        $client->expects($this->once())
            ->method('setGarage')
            ->with($this->garage);

        $this->garage->addClient($client);

        $this->assertCount(1, $this->garage->getClients());
        $this->assertTrue($this->garage->getClients()->contains($client));
    }

    /**
     * Test removing a client
     */
    public function testRemoveClient(): void
    {
        $client = $this->createMock(\App\Entity\Client::class);
        // setGarage is called twice: once with $this->garage, once with null
        $client->expects($this->exactly(2))->method('setGarage');
        $client->expects($this->once())->method('getGarage')->willReturn($this->garage);

        $this->garage->addClient($client);
        $this->assertCount(1, $this->garage->getClients());

        $this->garage->removeClient($client);
        $this->assertCount(0, $this->garage->getClients());
    }
}
