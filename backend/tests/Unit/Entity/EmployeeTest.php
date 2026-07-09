<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Employee;
use App\Entity\Garage;
use PHPUnit\Framework\TestCase;

class EmployeeTest extends TestCase
{
    private Employee $employee;

    protected function setUp(): void
    {
        // Create anonymous class since Employee is abstract
        $this->employee = new class extends Employee {
        };
    }

    public function testSetAndGetFirstName(): void
    {
        $firstName = 'John';
        $this->employee->setFirstName($firstName);
        $this->assertSame($firstName, $this->employee->getFirstName());
    }

    public function testSetAndGetLastName(): void
    {
        $lastName = 'Doe';
        $this->employee->setLastName($lastName);
        $this->assertSame($lastName, $this->employee->getLastName());
    }

    public function testSetAndGetBirthDate(): void
    {
        $birthDate = new \DateTimeImmutable('1990-01-15');
        $this->employee->setBirthDate($birthDate);
        $this->assertSame($birthDate, $this->employee->getBirthDate());
    }

    public function testSetAndGetStartDate(): void
    {
        $startDate = new \DateTimeImmutable('2020-06-01');
        $this->employee->setStartDate($startDate);
        $this->assertSame($startDate, $this->employee->getStartDate());
    }

    public function testStartDateCanBeNull(): void
    {
        $this->employee->setStartDate(null);
        $this->assertNull($this->employee->getStartDate());
    }

    public function testGetGaragesReturnsEmptyCollectionByDefault(): void
    {
        $garages = $this->employee->getGarages();
        $this->assertCount(0, $garages);
    }

    public function testAddGarage(): void
    {
        $garage = $this->createMock(Garage::class);
        $garage->expects($this->once())
            ->method('addEmployee')
            ->with($this->employee);

        $this->employee->addGarage($garage);

        $this->assertCount(1, $this->employee->getGarages());
        $this->assertTrue($this->employee->getGarages()->contains($garage));
    }

    public function testAddGarageDoesNotDuplicate(): void
    {
        $garage = $this->createMock(Garage::class);
        $garage->expects($this->once())
            ->method('addEmployee');

        $this->employee->addGarage($garage);
        $this->employee->addGarage($garage);

        $this->assertCount(1, $this->employee->getGarages());
    }

    public function testRemoveGarage(): void
    {
        $garage = $this->createMock(Garage::class);
        $garage->method('addEmployee');

        $this->employee->addGarage($garage);
        $this->assertCount(1, $this->employee->getGarages());

        $garage->expects($this->once())
            ->method('removeEmployee')
            ->with($this->employee);

        $this->employee->removeGarage($garage);
        $this->assertCount(0, $this->employee->getGarages());
    }

    public function testFluentInterface(): void
    {
        $result = $this->employee
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setBirthDate(new \DateTimeImmutable('1990-01-15'))
            ->setStartDate(new \DateTimeImmutable('2020-06-01'));

        $this->assertSame($this->employee, $result);
    }
}
