<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Car;
use App\Entity\Client;
use App\Entity\Garage;
use App\Entity\Intervention;
use App\Entity\InterventionTask;
use App\Entity\Mechanic;
use App\Enum\DocumentType;
use App\Enum\InterventionStatus;
use App\Enum\InterventionType;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class InterventionTest extends TestCase
{
    private Intervention $intervention;

    protected function setUp(): void
    {
        $this->intervention = new Intervention();
    }

    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $this->assertNull($this->intervention->getId());
    }

    public function testSetAndGetDate(): void
    {
        $date = new \DateTimeImmutable('2024-01-15');
        $this->intervention->setDate($date);
        $this->assertSame($date, $this->intervention->getDate());
    }

    public function testSetAndGetStartTime(): void
    {
        $time = new \DateTimeImmutable('09:00');
        $this->intervention->setStartTime($time);
        $this->assertSame($time, $this->intervention->getStartTime());
    }

    public function testSetAndGetStatus(): void
    {
        $status = InterventionStatus::InProgress;
        $this->intervention->setStatus($status);
        $this->assertSame($status, $this->intervention->getStatus());
    }

    public function testSetAndGetType(): void
    {
        $type = InterventionType::Diagnostic;
        $this->intervention->setType($type);
        $this->assertSame($type, $this->intervention->getType());
    }

    public function testSetAndGetDocumentType(): void
    {
        $docType = DocumentType::Invoice;
        $this->intervention->setDocumentType($docType);
        $this->assertSame($docType, $this->intervention->getDocumentType());
    }

    public function testSetAndGetClientRequest(): void
    {
        $request = 'Vidange + filtre';
        $this->intervention->setClientRequest($request);
        $this->assertSame($request, $this->intervention->getClientRequest());
    }

    public function testClientRequestCanBeNull(): void
    {
        $this->intervention->setClientRequest(null);
        $this->assertNull($this->intervention->getClientRequest());
    }

    public function testSetAndGetFinalNote(): void
    {
        $note = 'Travaux effectués';
        $this->intervention->setFinalNote($note);
        $this->assertSame($note, $this->intervention->getFinalNote());
    }

    public function testFinalNoteCanBeNull(): void
    {
        $this->intervention->setFinalNote(null);
        $this->assertNull($this->intervention->getFinalNote());
    }

    public function testSetAndGetCar(): void
    {
        $car = $this->createStub(Car::class);
        $this->intervention->setCar($car);
        $this->assertSame($car, $this->intervention->getCar());
    }

    public function testGetMechanicsReturnsEmptyCollectionByDefault(): void
    {
        $mechanics = $this->intervention->getMechanics();
        $this->assertCount(0, $mechanics);
    }

    public function testAddMechanic(): void
    {
        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->expects($this->once())
            ->method('addIntervention')
            ->with($this->intervention);

        $this->intervention->addMechanic($mechanic);

        $this->assertCount(1, $this->intervention->getMechanics());
        $this->assertTrue($this->intervention->getMechanics()->contains($mechanic));
    }

    public function testAddMechanicDoesNotDuplicate(): void
    {
        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->expects($this->once())
            ->method('addIntervention');

        $this->intervention->addMechanic($mechanic);
        $this->intervention->addMechanic($mechanic);

        $this->assertCount(1, $this->intervention->getMechanics());
    }

    public function testGetInterventionTasksReturnsEmptyCollectionByDefault(): void
    {
        $tasks = $this->intervention->getInterventionTasks();
        $this->assertCount(0, $tasks);
    }

    public function testAddInterventionTask(): void
    {
        $task = $this->createMock(InterventionTask::class);
        $task->expects($this->once())
            ->method('setIntervention')
            ->with($this->intervention);

        $this->intervention->addInterventionTask($task);

        $this->assertCount(1, $this->intervention->getInterventionTasks());
        $this->assertTrue($this->intervention->getInterventionTasks()->contains($task));
    }

    public function testAddInterventionTaskDoesNotDuplicate(): void
    {
        $task = $this->createMock(InterventionTask::class);
        $task->expects($this->once())
            ->method('setIntervention');

        $this->intervention->addInterventionTask($task);
        $this->intervention->addInterventionTask($task);

        $this->assertCount(1, $this->intervention->getInterventionTasks());
    }

    public function testValidateMechanicsInSameGarageWithNoViolations(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);
        $garage->method('getName')->willReturn('Garage Test');

        $client = $this->createStub(Client::class);
        $client->method('getGarage')->willReturn($garage);

        $car = $this->createStub(Car::class);
        $car->method('getClient')->willReturn($client);

        $this->intervention->setCar($car);

        $mechanic = $this->createStub(Mechanic::class);
        $mechanic->method('getFirstName')->willReturn('John');
        $mechanic->method('getLastName')->willReturn('Doe');

        $garages = new ArrayCollection([$garage]);
        $mechanic->method('getGarages')->willReturn($garages);

        $this->intervention->addMechanic($mechanic);

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');

        $this->intervention->validateMechanicsInSameGarage($context);
    }

    public function testValidateMechanicsInSameGarageWithViolation(): void
    {
        $interventionGarage = $this->createStub(Garage::class);
        $interventionGarage->method('getId')->willReturn(1);
        $interventionGarage->method('getName')->willReturn('Garage A');

        $mechanicGarage = $this->createStub(Garage::class);
        $mechanicGarage->method('getId')->willReturn(2);
        $mechanicGarage->method('getName')->willReturn('Garage B');

        $client = $this->createStub(Client::class);
        $client->method('getGarage')->willReturn($interventionGarage);

        $car = $this->createStub(Car::class);
        $car->method('getClient')->willReturn($client);

        $this->intervention->setCar($car);

        $mechanic = $this->createStub(Mechanic::class);
        $mechanic->method('getFirstName')->willReturn('John');
        $mechanic->method('getLastName')->willReturn('Doe');

        $garages = new ArrayCollection([$mechanicGarage]);
        $mechanic->method('getGarages')->willReturn($garages);

        $this->intervention->addMechanic($mechanic);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->method('atPath')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->with('Mechanic "{{ name }}" does not belong to garage "{{ garage }}".')
            ->willReturn($violationBuilder);

        $this->intervention->validateMechanicsInSameGarage($context);
    }

    public function testFluentInterface(): void
    {
        $result = $this->intervention
            ->setDate(new \DateTimeImmutable('2024-01-15'))
            ->setStartTime(new \DateTimeImmutable('09:00'))
            ->setStatus(InterventionStatus::Assigned)
            ->setType(InterventionType::Repair);

        $this->assertSame($this->intervention, $result);
    }

    /**
     * Test removing an intervention task
     */
    public function testRemoveInterventionTask(): void
    {
        $task = $this->createMock(InterventionTask::class);
        // setIntervention is called twice: once with $this->intervention, once with null
        $task->expects($this->exactly(2))->method('setIntervention');
        $task->expects($this->once())->method('getIntervention')->willReturn($this->intervention);

        $this->intervention->addInterventionTask($task);
        $this->assertCount(1, $this->intervention->getInterventionTasks());

        $this->intervention->removeInterventionTask($task);
        $this->assertCount(0, $this->intervention->getInterventionTasks());
    }

    /**
     * Test removing a mechanic
     */
    public function testRemoveMechanic(): void
    {
        $mechanic = $this->createMock(Mechanic::class);
        $mechanic->expects($this->once())->method('addIntervention')->with($this->intervention);
        $mechanic->expects($this->once())->method('removeIntervention')->with($this->intervention);

        $this->intervention->addMechanic($mechanic);
        $this->assertCount(1, $this->intervention->getMechanics());

        $this->intervention->removeMechanic($mechanic);
        $this->assertCount(0, $this->intervention->getMechanics());
    }
}
