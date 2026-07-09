<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Garage;
use App\Entity\Receptionist;
use App\Repository\GarageRepository;
use App\Repository\ReceptionistRepository;
use App\Service\ReceptionistService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AllowMockObjectsWithoutExpectations]
class ReceptionistServiceTest extends TestCase
{
    private ReceptionistRepository $receptionistRepository;
    private GarageRepository $garageRepository;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private ReceptionistService $receptionistService;

    protected function setUp(): void
    {
        $this->receptionistRepository = $this->createMock(ReceptionistRepository::class);
        $this->garageRepository = $this->createMock(GarageRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->receptionistService = new ReceptionistService(
            $this->receptionistRepository,
            $this->garageRepository,
            $this->entityManager,
            $this->validator
        );
    }

    /**
     * Test getReceptionistsByGarageId returns empty array when no receptionists
     */
    public function testGetReceptionistsByGarageIdReturnsEmptyArray(): void
    {
        $this->receptionistRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $result = $this->receptionistService->getReceptionistsByGarageId(1);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Test getReceptionistsByGarageId returns formatted receptionists
     */
    public function testGetReceptionistsByGarageIdReturnsFormattedReceptionists(): void
    {
        $receptionist1 = $this->createReceptionistStub(
            1,
            'Dupont',
            'Sophie',
            new \DateTimeImmutable('1988-03-10'),
            new \DateTimeImmutable('2019-09-01'),
            'sophie.dupont@example.com'
        );

        $receptionist2 = $this->createReceptionistStub(
            2,
            'Bernard',
            'Claire',
            new \DateTimeImmutable('1992-11-25'),
            null,
            'claire.bernard@example.com'
        );

        $this->receptionistRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([$receptionist1, $receptionist2]);

        $result = $this->receptionistService->getReceptionistsByGarageId(1);

        $this->assertCount(2, $result);

        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Dupont', $result[0]['lastName']);
        $this->assertEquals('Sophie', $result[0]['firstName']);
        $this->assertEquals('1988-03-10', $result[0]['birthDate']);
        $this->assertEquals('2019-09-01', $result[0]['hireDate']);
        $this->assertEquals('sophie.dupont@example.com', $result[0]['email']);

        $this->assertEquals(2, $result[1]['id']);
        $this->assertEquals('Bernard', $result[1]['lastName']);
        $this->assertEquals('Claire', $result[1]['firstName']);
        $this->assertEquals('1992-11-25', $result[1]['birthDate']);
        $this->assertNull($result[1]['hireDate']);
        $this->assertEquals('claire.bernard@example.com', $result[1]['email']);
    }

    /**
     * Test getReceptionistsByGarageId formats dates correctly
     */
    public function testGetReceptionistsByGarageIdFormatsDateCorrectly(): void
    {
        $receptionist = $this->createReceptionistStub(
            1,
            'Test',
            'User',
            new \DateTimeImmutable('1995-06-20'),
            new \DateTimeImmutable('2022-01-15'),
            'test@example.com'
        );

        $this->receptionistRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(3)
            ->willReturn([$receptionist]);

        $result = $this->receptionistService->getReceptionistsByGarageId(3);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result[0]['birthDate']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result[0]['hireDate']);
    }

    /**
     * Test createReceptionist throws exception when garage not found
     */
    public function testCreateReceptionistThrowsExceptionWhenGarageNotFound(): void
    {
        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Garage not found');

        $this->receptionistService->createReceptionist(999, [
            'lastName' => 'Doe',
            'firstName' => 'Jane',
            'birthDate' => '1990-05-15',
            'email' => 'jane.doe@example.com',
            'password' => 'securePassword123',
        ]);
    }

    /**
     * Test createReceptionist throws exception when birthDate format invalid
     */
    public function testCreateReceptionistThrowsExceptionWhenBirthDateInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid birthDate format. Expected Y-m-d');

        $this->receptionistService->createReceptionist(1, [
            'lastName' => 'Doe',
            'firstName' => 'Jane',
            'birthDate' => '15/05/1990', // Wrong format
            'email' => 'jane.doe@example.com',
            'password' => 'securePassword123',
        ]);
    }

    /**
     * Test createReceptionist throws exception when hireDate format invalid
     */
    public function testCreateReceptionistThrowsExceptionWhenHireDateInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid hireDate format. Expected Y-m-d');

        $this->receptionistService->createReceptionist(1, [
            'lastName' => 'Doe',
            'firstName' => 'Jane',
            'birthDate' => '1990-05-15',
            'hireDate' => 'invalid-date',
            'email' => 'jane.doe@example.com',
            'password' => 'securePassword123',
        ]);
    }

    /**
     * Test createReceptionist throws exception when validation fails
     */
    public function testCreateReceptionistThrowsExceptionWhenValidationFails(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $violation = $this->createStub(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Email is invalid');

        $violations = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed: Email is invalid');

        $this->receptionistService->createReceptionist(1, [
            'lastName' => 'Doe',
            'firstName' => 'Jane',
            'birthDate' => '1990-05-15',
            'email' => 'invalid-email',
            'password' => 'securePassword123',
        ]);
    }

    /**
     * Test createReceptionist successfully creates receptionist
     */
    public function testCreateReceptionistSuccessfullyCreatesReceptionist(): void
    {
        $garage = $this->createMock(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Receptionist::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->receptionistService->createReceptionist(1, [
            'lastName' => 'Doe',
            'firstName' => 'Jane',
            'birthDate' => '1990-05-15',
            'hireDate' => '2020-03-01',
            'email' => 'jane.doe@example.com',
            'password' => 'securePassword123',
        ]);

        $this->assertInstanceOf(Receptionist::class, $result);
    }

    /**
     * Test createReceptionist handles optional hireDate
     */
    public function testCreateReceptionistHandlesOptionalHireDate(): void
    {
        $garage = $this->createMock(Garage::class);
        $garage->method('getId')->willReturn(1);

        $this->garageRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($garage);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->receptionistService->createReceptionist(1, [
            'lastName' => 'Doe',
            'firstName' => 'Jane',
            'birthDate' => '1990-05-15',
            'email' => 'jane.doe@example.com',
            'password' => 'securePassword123',
            // hireDate omitted
        ]);

        $this->assertInstanceOf(Receptionist::class, $result);
    }

    /**
     * Test updateReceptionist throws exception when receptionist not found
     */
    public function testUpdateReceptionistThrowsExceptionWhenReceptionistNotFound(): void
    {
        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Receptionist not found');

        $this->receptionistService->updateReceptionist(999, 1, []);
    }

    /**
     * Test updateReceptionist throws exception when receptionist not in garage
     */
    public function testUpdateReceptionistThrowsExceptionWhenUnauthorized(): void
    {
        $garage1 = $this->createStub(Garage::class);
        $garage1->method('getId')->willReturn(1);

        $garage2 = $this->createStub(Garage::class);
        $garage2->method('getId')->willReturn(2);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage2]));

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unauthorized: receptionist does not belong to your garage');

        $this->receptionistService->updateReceptionist(1, 1, []);
    }

    /**
     * Test updateReceptionist updates personal information
     */
    public function testUpdateReceptionistUpdatesPersonalInformation(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $receptionist->expects($this->once())->method('setLastName')->with('NewLastName')->willReturnSelf();
        $receptionist->expects($this->once())->method('setFirstName')->with('NewFirstName')->willReturnSelf();

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->receptionistService->updateReceptionist(1, 1, [
            'lastName' => 'NewLastName',
            'firstName' => 'NewFirstName',
        ]);

        $this->assertInstanceOf(Receptionist::class, $result);
    }

    /**
     * Test updateReceptionist updates birthDate
     */
    public function testUpdateReceptionistUpdatesBirthDate(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $receptionist->expects($this->once())
            ->method('setBirthDate')
            ->with($this->callback(function ($date) {
                return $date instanceof \DateTimeImmutable && $date->format('Y-m-d') === '1993-07-12';
            }))
            ->willReturnSelf();

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->receptionistService->updateReceptionist(1, 1, [
            'birthDate' => '1993-07-12',
        ]);
    }

    /**
     * Test updateReceptionist throws exception when birthDate format invalid
     */
    public function testUpdateReceptionistThrowsExceptionWhenBirthDateInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid birthDate format. Expected Y-m-d');

        $this->receptionistService->updateReceptionist(1, 1, [
            'birthDate' => 'not-a-valid-date',
        ]);
    }

    /**
     * Test updateReceptionist updates hireDate
     */
    public function testUpdateReceptionistUpdatesHireDate(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $receptionist->expects($this->once())
            ->method('setStartDate')
            ->with($this->callback(function ($date) {
                return $date instanceof \DateTimeImmutable && $date->format('Y-m-d') === '2023-02-01';
            }))
            ->willReturnSelf();

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->receptionistService->updateReceptionist(1, 1, [
            'hireDate' => '2023-02-01',
        ]);
    }

    /**
     * Test updateReceptionist clears hireDate when empty string provided
     */
    public function testUpdateReceptionistClearsHireDateWhenEmptyString(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $receptionist->expects($this->once())
            ->method('setStartDate')
            ->with(null)
            ->willReturnSelf();

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->receptionistService->updateReceptionist(1, 1, [
            'hireDate' => '',
        ]);
    }

    /**
     * Test updateReceptionist throws exception when hireDate format invalid
     */
    public function testUpdateReceptionistThrowsExceptionWhenHireDateInvalid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid hireDate format. Expected Y-m-d');

        $this->receptionistService->updateReceptionist(1, 1, [
            'hireDate' => 'invalid-format',
        ]);
    }

    /**
     * Test updateReceptionist updates email
     */
    public function testUpdateReceptionistUpdatesEmail(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $receptionist->expects($this->once())
            ->method('setEmail')
            ->with('newemail@example.com')
            ->willReturnSelf();

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->receptionistService->updateReceptionist(1, 1, [
            'email' => 'newemail@example.com',
        ]);
    }

    /**
     * Test updateReceptionist updates password when provided
     */
    public function testUpdateReceptionistUpdatesPasswordWhenProvided(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $receptionist->expects($this->once())
            ->method('setPassword')
            ->with('newSecurePassword456')
            ->willReturnSelf();

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->receptionistService->updateReceptionist(1, 1, [
            'password' => 'newSecurePassword456',
        ]);
    }

    /**
     * Test updateReceptionist does not update password when not provided
     */
    public function testUpdateReceptionistDoesNotUpdatePasswordWhenNotProvided(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $receptionist->expects($this->never())->method('setPassword');

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('flush');

        $this->receptionistService->updateReceptionist(1, 1, [
            'firstName' => 'UpdatedName',
            // password not provided
        ]);
    }

    /**
     * Test updateReceptionist throws exception when validation fails
     */
    public function testUpdateReceptionistThrowsExceptionWhenValidationFails(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createMock(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $violation = $this->createStub(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Validation error');

        $violations = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed: Validation error');

        $this->receptionistService->updateReceptionist(1, 1, []);
    }

    /**
     * Test deleteReceptionist throws exception when receptionist not found
     */
    public function testDeleteReceptionistThrowsExceptionWhenReceptionistNotFound(): void
    {
        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Receptionist not found');

        $this->receptionistService->deleteReceptionist(999, 1);
    }

    /**
     * Test deleteReceptionist throws exception when receptionist not in garage
     */
    public function testDeleteReceptionistThrowsExceptionWhenUnauthorized(): void
    {
        $garage1 = $this->createStub(Garage::class);
        $garage1->method('getId')->willReturn(1);

        $garage2 = $this->createStub(Garage::class);
        $garage2->method('getId')->willReturn(2);

        $receptionist = $this->createStub(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage2]));

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($receptionist);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unauthorized: receptionist does not belong to your garage');

        $this->receptionistService->deleteReceptionist(1, 1);
    }

    /**
     * Test deleteReceptionist successfully deletes receptionist
     */
    public function testDeleteReceptionistSuccessfullyDeletesReceptionist(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $receptionist = $this->createStub(Receptionist::class);
        $receptionist->method('getGarages')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$garage]));

        $this->receptionistRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($receptionist);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($receptionist);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->receptionistService->deleteReceptionist(5, 1);
    }

    /**
     * Helper method to create a Receptionist stub
     */
    private function createReceptionistStub(
        int $id,
        string $lastName,
        string $firstName,
        \DateTimeImmutable $birthDate,
        ?\DateTimeImmutable $startDate,
        string $email
    ): Receptionist {
        $receptionist = $this->createStub(Receptionist::class);

        $receptionist->method('getId')->willReturn($id);
        $receptionist->method('getLastName')->willReturn($lastName);
        $receptionist->method('getFirstName')->willReturn($firstName);
        $receptionist->method('getBirthDate')->willReturn($birthDate);
        $receptionist->method('getStartDate')->willReturn($startDate);
        $receptionist->method('getEmail')->willReturn($email);

        return $receptionist;
    }
}
