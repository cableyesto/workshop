<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\MechanicRepository;
use Illuminate\Support\Collection;

final class MechanicService
{
    public function __construct(
        private readonly MechanicRepository $mechanicRepository,
    ) {
    }

    /**
     * Get all mechanics for a specific garage
     *
     * @return array<int, array{id: int, lastName: string, firstName: string, birthDate: string, hireDate: string}>
     */
    public function getMechanicsByGarageId(int $garageId): array
    {
        $mechanics = $this->mechanicRepository->findByGarage($garageId);

        return Collection::make($mechanics)
            ->map(fn($mechanic) => [
                'id' => $mechanic->getId(),
                'lastName' => $mechanic->getLastName(),
                'firstName' => $mechanic->getFirstName(),
                'birthDate' => $mechanic->getBirthDate()->format('Y-m-d'),
                'hireDate' => $mechanic->getStartDate()?->format('Y-m-d'),
            ])
            ->values()
            ->toArray();
    }
}
