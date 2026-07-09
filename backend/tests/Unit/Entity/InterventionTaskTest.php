<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Intervention;
use App\Entity\InterventionTask;
use App\Entity\ServiceTask;
use PHPUnit\Framework\TestCase;

class InterventionTaskTest extends TestCase
{
    private InterventionTask $interventionTask;

    protected function setUp(): void
    {
        $this->interventionTask = new InterventionTask();
    }

    public function testSetAndGetIntervention(): void
    {
        $intervention = $this->createStub(Intervention::class);
        $this->interventionTask->setIntervention($intervention);
        $this->assertSame($intervention, $this->interventionTask->getIntervention());
    }

    public function testSetAndGetServiceTask(): void
    {
        $serviceTask = $this->createStub(ServiceTask::class);
        $this->interventionTask->setServiceTask($serviceTask);
        $this->assertSame($serviceTask, $this->interventionTask->getServiceTask());
    }

    public function testFluentInterface(): void
    {
        $intervention = $this->createStub(Intervention::class);
        $serviceTask = $this->createStub(ServiceTask::class);

        $result = $this->interventionTask
            ->setIntervention($intervention)
            ->setServiceTask($serviceTask);

        $this->assertSame($this->interventionTask, $result);
    }
}
