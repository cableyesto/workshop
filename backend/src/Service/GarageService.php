<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Garage;
use App\Repository\GarageRepository;
use Illuminate\Support\Collection;

final class GarageService
{
    public function __construct(
        private readonly GarageRepository $garageRepository,
    ) {
    }

    /**
     * Get garage by ID with time slots.
     *
     * @return array{id: int, name: string, siret: string, timeSlots: array}|null
     */
    public function getGarageById(int $id): ?array
    {
        // Fetch garage with time_slots joined
        $garage = $this->garageRepository->findWithTimeSlots($id);

        if (!$garage) {
            return null;
        }

        $timeSlots = Collection::make($garage->getTimeSlots())
            ->map(fn($timeSlot) => [
                'dayOfWeek' => $timeSlot->getDayOfWeek(),
                'startTime' => $timeSlot->getStartTime()?->format('H:i'),
                'endTime' => $timeSlot->getEndTime()?->format('H:i'),
            ])
            ->values()
            ->toArray();

        return [
            'id' => $garage->getId(),
            'name' => $garage->getName(),
            'siret' => $garage->getSiretNumber(),
            'timeSlots' => $timeSlots,
        ];
    }
}
