<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Garage;
use App\Entity\TimeSlot;
use App\Enum\DayOfWeek;
use PHPUnit\Framework\TestCase;

class TimeSlotTest extends TestCase
{
    private TimeSlot $timeSlot;

    protected function setUp(): void
    {
        $this->timeSlot = new TimeSlot();
    }

    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $this->assertNull($this->timeSlot->getId());
    }

    public function testSetAndGetDayOfWeek(): void
    {
        $day = DayOfWeek::Lundi;
        $this->timeSlot->setDayOfWeek($day);
        $this->assertSame($day, $this->timeSlot->getDayOfWeek());
    }

    public function testSetAndGetStartTime(): void
    {
        $startTime = new \DateTimeImmutable('08:00');
        $this->timeSlot->setStartTime($startTime);
        $this->assertSame($startTime, $this->timeSlot->getStartTime());
    }

    public function testSetAndGetEndTime(): void
    {
        $endTime = new \DateTimeImmutable('12:00');
        $this->timeSlot->setEndTime($endTime);
        $this->assertSame($endTime, $this->timeSlot->getEndTime());
    }

    public function testSetAndGetGarage(): void
    {
        $garage = $this->createStub(Garage::class);
        $this->timeSlot->setGarage($garage);
        $this->assertSame($garage, $this->timeSlot->getGarage());
    }

    public function testFluentInterface(): void
    {
        $result = $this->timeSlot
            ->setDayOfWeek(DayOfWeek::Lundi)
            ->setStartTime(new \DateTimeImmutable('08:00'))
            ->setEndTime(new \DateTimeImmutable('17:00'));

        $this->assertSame($this->timeSlot, $result);
    }
}
