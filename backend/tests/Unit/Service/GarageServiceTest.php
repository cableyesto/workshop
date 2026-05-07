<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Garage;
use App\Entity\TimeSlot;
use App\Enum\DayOfWeek;
use App\Repository\GarageRepository;
use App\Service\GarageService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class GarageServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private GarageRepository $mockRepository;
    private GarageService $garageService;

    protected function setUp(): void
    {
        $this->mockRepository = Mockery::mock(GarageRepository::class);
        $this->garageService = new GarageService($this->mockRepository);
    }

    /**
     * Test that getGarageById returns null when garage is not found
     */
    public function testReturnsNullWhenGarageNotFound(): void
    {
        $this->mockRepository
            ->shouldReceive('findWithTimeSlots')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->garageService->getGarageById(999);

        $this->assertNull($result);
    }

    /**
     * Test that getGarageById returns correct structure for garage without time slots
     */
    public function testReturnsCorrectStructureForGarageWithoutTimeSlots(): void
    {
        $mockGarage = Mockery::mock(Garage::class);
        $mockGarage->shouldReceive('getId')->andReturn(1);
        $mockGarage->shouldReceive('getName')->andReturn('Test Garage');
        $mockGarage->shouldReceive('getSiretNumber')->andReturn('12345678901234');
        $mockGarage->shouldReceive('getTimeSlots')->andReturn(new ArrayCollection([]));

        $this->mockRepository
            ->shouldReceive('findWithTimeSlots')
            ->once()
            ->with(1)
            ->andReturn($mockGarage);

        $result = $this->garageService->getGarageById(1);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('Test Garage', $result['name']);
        $this->assertSame('12345678901234', $result['siret']);
        $this->assertIsArray($result['timeSlots']);
        $this->assertEmpty($result['timeSlots']);
    }

    /**
     * Test that getGarageById returns correct structure for garage with one time slot
     */
    public function testReturnsCorrectStructureForGarageWithOneTimeSlot(): void
    {
        $mockTimeSlot = Mockery::mock(TimeSlot::class);
        $mockTimeSlot->shouldReceive('getDayOfWeek')->andReturn(DayOfWeek::Lundi);
        $mockTimeSlot->shouldReceive('getStartTime')->andReturn(new \DateTimeImmutable('09:00'));
        $mockTimeSlot->shouldReceive('getEndTime')->andReturn(new \DateTimeImmutable('17:00'));

        $mockGarage = Mockery::mock(Garage::class);
        $mockGarage->shouldReceive('getId')->andReturn(1);
        $mockGarage->shouldReceive('getName')->andReturn('Garage One Slot');
        $mockGarage->shouldReceive('getSiretNumber')->andReturn('98765432109876');
        $mockGarage->shouldReceive('getTimeSlots')->andReturn(new ArrayCollection([$mockTimeSlot]));

        $this->mockRepository
            ->shouldReceive('findWithTimeSlots')
            ->once()
            ->with(1)
            ->andReturn($mockGarage);

        $result = $this->garageService->getGarageById(1);

        $this->assertIsArray($result);
        $this->assertCount(1, $result['timeSlots']);
        $this->assertSame(DayOfWeek::Lundi, $result['timeSlots'][0]['dayOfWeek']);
        $this->assertSame('09:00', $result['timeSlots'][0]['startTime']);
        $this->assertSame('17:00', $result['timeSlots'][0]['endTime']);
    }

    /**
     * Test that getGarageById returns correct structure for garage with multiple time slots
     */
    public function testReturnsCorrectStructureForGarageWithMultipleTimeSlots(): void
    {
        $mockTimeSlot1 = Mockery::mock(TimeSlot::class);
        $mockTimeSlot1->shouldReceive('getDayOfWeek')->andReturn(DayOfWeek::Lundi);
        $mockTimeSlot1->shouldReceive('getStartTime')->andReturn(new \DateTimeImmutable('09:00'));
        $mockTimeSlot1->shouldReceive('getEndTime')->andReturn(new \DateTimeImmutable('12:00'));

        $mockTimeSlot2 = Mockery::mock(TimeSlot::class);
        $mockTimeSlot2->shouldReceive('getDayOfWeek')->andReturn(DayOfWeek::Lundi);
        $mockTimeSlot2->shouldReceive('getStartTime')->andReturn(new \DateTimeImmutable('13:00'));
        $mockTimeSlot2->shouldReceive('getEndTime')->andReturn(new \DateTimeImmutable('18:00'));

        $mockTimeSlot3 = Mockery::mock(TimeSlot::class);
        $mockTimeSlot3->shouldReceive('getDayOfWeek')->andReturn(DayOfWeek::Mardi);
        $mockTimeSlot3->shouldReceive('getStartTime')->andReturn(new \DateTimeImmutable('08:00'));
        $mockTimeSlot3->shouldReceive('getEndTime')->andReturn(new \DateTimeImmutable('16:00'));

        $mockGarage = Mockery::mock(Garage::class);
        $mockGarage->shouldReceive('getId')->andReturn(2);
        $mockGarage->shouldReceive('getName')->andReturn('Garage Multiple Slots');
        $mockGarage->shouldReceive('getSiretNumber')->andReturn('11111111111111');
        $mockGarage->shouldReceive('getTimeSlots')->andReturn(
            new ArrayCollection([$mockTimeSlot1, $mockTimeSlot2, $mockTimeSlot3])
        );

        $this->mockRepository
            ->shouldReceive('findWithTimeSlots')
            ->once()
            ->with(2)
            ->andReturn($mockGarage);

        $result = $this->garageService->getGarageById(2);

        $this->assertIsArray($result);
        $this->assertCount(3, $result['timeSlots']);

        // Verify first slot
        $this->assertSame(DayOfWeek::Lundi, $result['timeSlots'][0]['dayOfWeek']);
        $this->assertSame('09:00', $result['timeSlots'][0]['startTime']);
        $this->assertSame('12:00', $result['timeSlots'][0]['endTime']);

        // Verify second slot
        $this->assertSame(DayOfWeek::Lundi, $result['timeSlots'][1]['dayOfWeek']);
        $this->assertSame('13:00', $result['timeSlots'][1]['startTime']);
        $this->assertSame('18:00', $result['timeSlots'][1]['endTime']);

        // Verify third slot
        $this->assertSame(DayOfWeek::Mardi, $result['timeSlots'][2]['dayOfWeek']);
        $this->assertSame('08:00', $result['timeSlots'][2]['startTime']);
        $this->assertSame('16:00', $result['timeSlots'][2]['endTime']);
    }

    /**
     * Test that time is correctly formatted from DateTime to H:i string
     */
    public function testCorrectlyFormatsTimeFromDateTimeToHiString(): void
    {
        $mockTimeSlot = Mockery::mock(TimeSlot::class);
        $mockTimeSlot->shouldReceive('getDayOfWeek')->andReturn(DayOfWeek::Mercredi);
        $mockTimeSlot->shouldReceive('getStartTime')->andReturn(new \DateTimeImmutable('08:30:00'));
        $mockTimeSlot->shouldReceive('getEndTime')->andReturn(new \DateTimeImmutable('18:45:00'));

        $mockGarage = Mockery::mock(Garage::class);
        $mockGarage->shouldReceive('getId')->andReturn(3);
        $mockGarage->shouldReceive('getName')->andReturn('Garage Format Test');
        $mockGarage->shouldReceive('getSiretNumber')->andReturn('22222222222222');
        $mockGarage->shouldReceive('getTimeSlots')->andReturn(new ArrayCollection([$mockTimeSlot]));

        $this->mockRepository
            ->shouldReceive('findWithTimeSlots')
            ->once()
            ->with(3)
            ->andReturn($mockGarage);

        $result = $this->garageService->getGarageById(3);

        // Assert time format is H:i (not H:i:s or DateTime object)
        $this->assertSame('08:30', $result['timeSlots'][0]['startTime']);
        $this->assertSame('18:45', $result['timeSlots'][0]['endTime']);
        $this->assertIsString($result['timeSlots'][0]['startTime']);
        $this->assertIsString($result['timeSlots'][0]['endTime']);
    }

    /**
     * Test that null time values are handled gracefully
     */
    public function testHandlesNullTimeValuesGracefully(): void
    {
        $mockTimeSlot = Mockery::mock(TimeSlot::class);
        $mockTimeSlot->shouldReceive('getDayOfWeek')->andReturn(DayOfWeek::Jeudi);
        $mockTimeSlot->shouldReceive('getStartTime')->andReturn(null);
        $mockTimeSlot->shouldReceive('getEndTime')->andReturn(null);

        $mockGarage = Mockery::mock(Garage::class);
        $mockGarage->shouldReceive('getId')->andReturn(4);
        $mockGarage->shouldReceive('getName')->andReturn('Garage Null Times');
        $mockGarage->shouldReceive('getSiretNumber')->andReturn('33333333333333');
        $mockGarage->shouldReceive('getTimeSlots')->andReturn(new ArrayCollection([$mockTimeSlot]));

        $this->mockRepository
            ->shouldReceive('findWithTimeSlots')
            ->once()
            ->with(4)
            ->andReturn($mockGarage);

        $result = $this->garageService->getGarageById(4);

        $this->assertIsArray($result);
        $this->assertCount(1, $result['timeSlots']);
        $this->assertNull($result['timeSlots'][0]['startTime']);
        $this->assertNull($result['timeSlots'][0]['endTime']);
    }

    /**
     * Test that Collection transformation produces indexed array (not associative)
     */
    public function testCollectionTransformationProducesIndexedArray(): void
    {
        $mockTimeSlot1 = Mockery::mock(TimeSlot::class);
        $mockTimeSlot1->shouldReceive('getDayOfWeek')->andReturn(DayOfWeek::Vendredi);
        $mockTimeSlot1->shouldReceive('getStartTime')->andReturn(new \DateTimeImmutable('10:00'));
        $mockTimeSlot1->shouldReceive('getEndTime')->andReturn(new \DateTimeImmutable('19:00'));

        $mockTimeSlot2 = Mockery::mock(TimeSlot::class);
        $mockTimeSlot2->shouldReceive('getDayOfWeek')->andReturn(DayOfWeek::Samedi);
        $mockTimeSlot2->shouldReceive('getStartTime')->andReturn(new \DateTimeImmutable('09:00'));
        $mockTimeSlot2->shouldReceive('getEndTime')->andReturn(new \DateTimeImmutable('13:00'));

        $mockGarage = Mockery::mock(Garage::class);
        $mockGarage->shouldReceive('getId')->andReturn(5);
        $mockGarage->shouldReceive('getName')->andReturn('Garage Collection Test');
        $mockGarage->shouldReceive('getSiretNumber')->andReturn('44444444444444');
        $mockGarage->shouldReceive('getTimeSlots')->andReturn(
            new ArrayCollection([$mockTimeSlot1, $mockTimeSlot2])
        );

        $this->mockRepository
            ->shouldReceive('findWithTimeSlots')
            ->once()
            ->with(5)
            ->andReturn($mockGarage);

        $result = $this->garageService->getGarageById(5);

        // Verify it's an indexed array with sequential integer keys
        $this->assertCount(2, $result['timeSlots']);
        $expectedKeys = [0, 1];
        $actualKeys = array_keys($result['timeSlots']);
        $this->assertSame($expectedKeys, $actualKeys);
    }
}
