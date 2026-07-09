<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Garage;
use App\Entity\Mechanic;
use App\Repository\GarageRepository;
use App\Repository\MechanicRepository;
use App\Service\MechanicService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AllowMockObjectsWithoutExpectations]
class MechanicServiceTest extends TestCase
{
    private MechanicRepository $mechanicRepository;
    private GarageRepository $garageRepository;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private MechanicService $mechanicService;

    protected function setUp(): void
    {
        $this->mechanicRepository = $this->createMock(MechanicRepository::class);
        $this->garageRepository = $this->createMock(GarageRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->mechanicService = new MechanicService(
            $this->mechanicRepository,
            $this->garageRepository,
            $this->entityManager,
            $this->validator
        );
    }

    /**
     * Test getMechanicsByGarageId returns empty array when no mechanics
     */
    public function testGetMechanicsByGarageIdReturnsEmptyArray(): void
    {
        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $result = $this->mechanicService->getMechanicsByGarageId(1);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Test getMechanicsByGarageId returns formatted mechanics
     */
    public function testGetMechanicsByGarageIdReturnsFormattedMechanics(): void
    {
        $mechanic1 = $this->createMechanicStub(
            1,
            'Dupont',
            'Jean',
            new \DateTimeImmutable('1985-05-15'),
            new \DateTimeImmutable('2020-01-10')
        );

        $mechanic2 = $this->createMechanicStub(
            2,
            'Martin',
            'Marie',
            new \DateTimeImmutable('1990-08-22'),
            null
        );

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([$mechanic1, $mechanic2]);

        $result = $this->mechanicService->getMechanicsByGarageId(1);

        $this->assertCount(2, $result);

        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Dupont', $result[0]['lastName']);
        $this->assertEquals('Jean', $result[0]['firstName']);
        $this->assertEquals('1985-05-15', $result[0]['birthDate']);
        $this->assertEquals('2020-01-10', $result[0]['hireDate']);

        $this->assertEquals(2, $result[1]['id']);
        $this->assertEquals('Martin', $result[1]['lastName']);
        $this->assertEquals('Marie', $result[1]['firstName']);
        $this->assertEquals('1990-08-22', $result[1]['birthDate']);
        $this->assertNull($result[1]['hireDate']);
    }

    /**
     * Test getMechanicsByGarageId formats dates correctly
     */
    public function testGetMechanicsByGarageIdFormatsDateCorrectly(): void
    {
        $mechanic = $this->createMechanicStub(
            1,
            'Test',
            'User',
            new \DateTimeImmutable('2000-12-31'),
            new \DateTimeImmutable('2023-06-15')
        );

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(5)
            ->willReturn([$mechanic]);

        $result = $this->mechanicService->getMechanicsByGarageId(5);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result[0]['birthDate']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result[0]['hireDate']);
    }

    /**
     * Test createMechanic throws exception when garage not found
     */
    public function testCreateMechanicThrowsExceptionWhenGarageNotFound(): void
    {
        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Garage not found');

        $this->mechanicService->createMechanic(999, [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => '1990-01-01',
            'pin' => '1234',
        ]);
    }

    /**
     * Test createMechanic throws exception when PIN format invalid
     */
    public function testCreateMechanicThrowsExceptionWhenPinInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        // findByGarage is NOT called because PIN validation fails first
        $this->mechanicRepository
            ->expects($this->never())
            ->method('findByGarage');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN must be exactly 4 digits');

        $this->mechanicService->createMechanic(1, [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => '1990-01-01',
            'pin' => '123', // Only 3 digits
        ]);
    }

    /**
     * Test createMechanic throws exception when PIN already used
     */
    public function testCreateMechanicThrowsExceptionWhenPinAlreadyUsed(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $existingMechanic = $this->createMock(Mechanic::class);
        $existingMechanic->method('getId')->willReturn(10);
        $existingMechanic->expects($this->once())->method('verifyPin')->with('1234')->willReturn(true);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([$existingMechanic]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN already in use in this garage');

        $this->mechanicService->createMechanic(1, [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => '1990-01-01',
            'pin' => '1234',
        ]);
    }

    /**
     * Test createMechanic throws exception when birthDate format invalid
     */
    public function testCreateMechanicThrowsExceptionWhenBirthDateInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid birthDate format. Expected Y-m-d');

        $this->mechanicService->createMechanic(1, [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => 'invalid-date',
            'pin' => '1234',
        ]);
    }

    /**
     * Test createMechanic throws exception when hireDate format invalid
     */
    public function testCreateMechanicThrowsExceptionWhenHireDateInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid hireDate format. Expected Y-m-d');

        $this->mechanicService->createMechanic(1, [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => '1990-01-01',
            'hireDate' => '01/01/2020', // Wrong format
            'pin' => '1234',
        ]);
    }

    /**
     * Test createMechanic throws exception when validation fails
     */
    public function testCreateMechanicThrowsExceptionWhenValidationFails(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $violation = $this->createStub(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Invalid data');

        $violations = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed: Invalid data');

        $this->mechanicService->createMechanic(1, [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => '1990-01-01',
            'pin' => '1234',
        ]);
    }

    /**
     * Test createMechanic successfully creates mechanic
     */
    public function testCreateMechanicSuccessfullyCreatesMechanic(): void
    {
        $garage = $this->createMock(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Mechanic::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->mechanicService->createMechanic(1, [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => '1990-01-01',
            'hireDate' => '2020-06-15',
            'pin' => '1234',
        ]);

        $this->assertInstanceOf(Mechanic::class, $result);
    }

    /**
     * Test createMechanic handles optional hireDate
     */
    public function testCreateMechanicHandlesOptionalHireDate(): void
    {
        $garage = $this->createMock(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->mechanicService->createMechanic(1, [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => '1990-01-01',
            'pin' => '1234',
            // hireDate omitted
        ]);

        $this->assertInstanceOf(Mechanic::class, $result);
    }

    /**
     * Test updateMechanic throws exception when mechanic not found
     */
    public function testUpdateMechanicThrowsExceptionWhenMechanicNotFound(): void
    {
        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Mechanic not found');

        $this->mechanicService->updateMechanic(999, 1, []);
    }

    /**
     * Test updateMechanic throws exception when mechanic not in garage
     */
    public function testUpdateMechanicThrowsExceptionWhenUnauthorized(): void
    {
        $garage1 = $this->createStub(Garage::class);
        $garage1->method('getId')->willReturn(1);

        $garage2 = $this->createStub(Garage::class);
        $garage2->method('getId')->willReturn(2);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage2]));

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unauthorized: mechanic does not belong to your garage');

        $this->mechanicService->updateMechanic(1, 1, []);
    }

    /**
     * Test updateMechanic updates personal information
     */
    public function testUpdateMechanicUpdatesPersonalInformation(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $mechanic->expects($this->once())->method('setLastName')->with('NewLastName')->willReturnSelf();
        $mechanic->expects($this->once())->method('setFirstName')->with('NewFirstName')->willReturnSelf();

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->mechanicService->updateMechanic(1, 1, [
            'lastName' => 'NewLastName',
            'firstName' => 'NewFirstName',
        ]);

        $this->assertInstanceOf(Mechanic::class, $result);
    }

    /**
     * Test updateMechanic updates birthDate
     */
    public function testUpdateMechanicUpdatesBirthDate(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $mechanic->expects($this->once())
            ->method('setBirthDate')
            ->with($this->callback(function ($date) {
                return $date instanceof \DateTimeImmutable && $date->format('Y-m-d') === '1995-03-20';
            }))
            ->willReturnSelf();

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->mechanicService->updateMechanic(1, 1, [
            'birthDate' => '1995-03-20',
        ]);
    }

    /**
     * Test updateMechanic throws exception when birthDate format invalid
     */
    public function testUpdateMechanicThrowsExceptionWhenBirthDateInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid birthDate format. Expected Y-m-d');

        $this->mechanicService->updateMechanic(1, 1, [
            'birthDate' => 'invalid-date',
        ]);
    }

    /**
     * Test updateMechanic updates hireDate
     */
    public function testUpdateMechanicUpdatesHireDate(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $mechanic->expects($this->once())
            ->method('setStartDate')
            ->with($this->callback(function ($date) {
                return $date instanceof \DateTimeImmutable && $date->format('Y-m-d') === '2021-07-01';
            }))
            ->willReturnSelf();

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->mechanicService->updateMechanic(1, 1, [
            'hireDate' => '2021-07-01',
        ]);
    }

    /**
     * Test updateMechanic clears hireDate when empty string provided
     */
    public function testUpdateMechanicClearsHireDateWhenEmptyString(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $mechanic->expects($this->once())
            ->method('setStartDate')
            ->with(null)
            ->willReturnSelf();

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->mechanicService->updateMechanic(1, 1, [
            'hireDate' => '',
        ]);
    }

    /**
     * Test updateMechanic throws exception when hireDate format invalid
     */
    public function testUpdateMechanicThrowsExceptionWhenHireDateInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid hireDate format. Expected Y-m-d');

        $this->mechanicService->updateMechanic(1, 1, [
            'hireDate' => 'not-a-date',
        ]);
    }

    /**
     * Test updateMechanic updates PIN when provided
     */
    public function testUpdateMechanicUpdatesPinWhenProvided(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getId')->willReturn(5);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $mechanic->expects($this->once())
            ->method('setPin')
            ->with('5678')
            ->willReturnSelf();

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($mechanic);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->mechanicService->updateMechanic(5, 1, [
            'pin' => '5678',
        ]);
    }

    /**
     * Test updateMechanic excludes current mechanic from PIN uniqueness check
     */
    public function testUpdateMechanicExcludesCurrentMechanicFromPinCheck(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getId')->willReturn(5);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));
        // verifyPin is NEVER called because this mechanic is excluded (same ID)
        $mechanic->expects($this->never())->method('verifyPin');

        $mechanic->expects($this->once())
            ->method('setPin')
            ->with('1234')
            ->willReturnSelf();

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($mechanic);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([$mechanic]); // Only the current mechanic has this PIN

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        // Should NOT throw exception
        $this->mechanicService->updateMechanic(5, 1, [
            'pin' => '1234',
        ]);
    }

    /**
     * Test updateMechanic throws exception when PIN already used by another mechanic
     */
    public function testUpdateMechanicThrowsExceptionWhenPinAlreadyUsed(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanicToUpdate = $this->createMock(Mechanic::class);
        $mechanicToUpdate->method('getId')->willReturn(5);
        $mechanicToUpdate->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $existingMechanic = $this->createMock(Mechanic::class);
        $existingMechanic->method('getId')->willReturn(10); // Different mechanic
        $existingMechanic->expects($this->once())->method('verifyPin')->with('9999')->willReturn(true);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($mechanicToUpdate);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([$mechanicToUpdate, $existingMechanic]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN already in use in this garage');

        $this->mechanicService->updateMechanic(5, 1, [
            'pin' => '9999',
        ]);
    }

    /**
     * Test updateMechanic does not update PIN when not provided
     */
    public function testUpdateMechanicDoesNotUpdatePinWhenNotProvided(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $mechanic->expects($this->never())->method('setPin');

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($mechanic);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->mechanicService->updateMechanic(5, 1, [
            'firstName' => 'John',
            // pin not provided
        ]);
    }

    /**
     * Test updateMechanic throws exception when validation fails
     */
    public function testUpdateMechanicThrowsExceptionWhenValidationFails(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $violation = $this->createStub(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Validation error');

        $violations = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed: Validation error');

        $this->mechanicService->updateMechanic(1, 1, []);
    }

    /**
     * Test deleteMechanic throws exception when mechanic not found
     */
    public function testDeleteMechanicThrowsExceptionWhenMechanicNotFound(): void
    {
        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Mechanic not found');

        $this->mechanicService->deleteMechanic(999, 1);
    }

    /**
     * Test deleteMechanic throws exception when mechanic not in garage
     */
    public function testDeleteMechanicThrowsExceptionWhenUnauthorized(): void
    {
        $garage1 = $this->createStub(Garage::class);
        $garage1->method('getId')->willReturn(1);

        $garage2 = $this->createStub(Garage::class);
        $garage2->method('getId')->willReturn(2);

        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage2]));

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mechanic);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unauthorized: mechanic does not belong to your garage');

        $this->mechanicService->deleteMechanic(1, 1);
    }

    /**
     * Test deleteMechanic successfully deletes mechanic
     */
    public function testDeleteMechanicSuccessfullyDeletesMechanic(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic = $this->createStub(Mechanic::class);
        $mechanic->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $this->mechanicRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($mechanic);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($mechanic);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->mechanicService->deleteMechanic(5, 1);
    }

    /**
     * Helper method to create a Mechanic stub
     */
    private function createMechanicStub(
        int $id,
        string $lastName,
        string $firstName,
        \DateTimeImmutable $birthDate,
        ?\DateTimeImmutable $startDate
    ): Mechanic {
        $mechanic = $this->createStub(Mechanic::class);

        $mechanic->method('getId')->willReturn($id);
        $mechanic->method('getLastName')->willReturn($lastName);
        $mechanic->method('getFirstName')->willReturn($firstName);
        $mechanic->method('getBirthDate')->willReturn($birthDate);
        $mechanic->method('getStartDate')->willReturn($startDate);

        return $mechanic;
    }
}
