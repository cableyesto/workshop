<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ServiceTask;
use PHPUnit\Framework\TestCase;

class ServiceTaskTest extends TestCase
{
    private ServiceTask $serviceTask;

    protected function setUp(): void
    {
        $this->serviceTask = new ServiceTask();
    }

    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $this->assertNull($this->serviceTask->getId());
    }

    public function testSetAndGetName(): void
    {
        $name = 'Vidange moteur';
        $this->serviceTask->setName($name);
        $this->assertSame($name, $this->serviceTask->getName());
    }

    public function testSetAndGetUnitPrice(): void
    {
        $price = '45.50';
        $this->serviceTask->setUnitPrice($price);
        $this->assertSame($price, $this->serviceTask->getUnitPrice());
    }

    public function testFluentInterface(): void
    {
        $result = $this->serviceTask
            ->setName('Changement pneus')
            ->setUnitPrice('75.00');

        $this->assertSame($this->serviceTask, $result);
    }

    public function testGetInterventionTasksReturnsEmptyCollectionByDefault(): void
    {
        $tasks = $this->serviceTask->getInterventionTasks();
        $this->assertCount(0, $tasks);
    }

    public function testAddInterventionTask(): void
    {
        $task = $this->createMock(\App\Entity\InterventionTask::class);
        $task->expects($this->once())
            ->method('setServiceTask')
            ->with($this->serviceTask);

        $this->serviceTask->addInterventionTask($task);

        $this->assertCount(1, $this->serviceTask->getInterventionTasks());
        $this->assertTrue($this->serviceTask->getInterventionTasks()->contains($task));
    }

    public function testAddInterventionTaskDoesNotDuplicate(): void
    {
        $task = $this->createMock(\App\Entity\InterventionTask::class);
        $task->expects($this->once())
            ->method('setServiceTask');

        $this->serviceTask->addInterventionTask($task);
        $this->serviceTask->addInterventionTask($task);

        $this->assertCount(1, $this->serviceTask->getInterventionTasks());
    }

    public function testRemoveInterventionTask(): void
    {
        $task = $this->createMock(\App\Entity\InterventionTask::class);
        $task->method('setServiceTask');

        $this->serviceTask->addInterventionTask($task);
        $this->assertCount(1, $this->serviceTask->getInterventionTasks());

        $task->expects($this->once())
            ->method('getServiceTask')
            ->willReturn($this->serviceTask);
        $task->expects($this->once())
            ->method('setServiceTask')
            ->with(null);

        $this->serviceTask->removeInterventionTask($task);
        $this->assertCount(0, $this->serviceTask->getInterventionTasks());
    }
}
