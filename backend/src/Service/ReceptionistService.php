<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ReceptionistRepository;
use Illuminate\Support\Collection;

final class ReceptionistService
{
    public function __construct(
        private readonly ReceptionistRepository $receptionistRepository,
    ) {
    }

    /**
     * Get all receptionists for a specific garage
     *
     * @return array<int, array{id: int, lastName: string, firstName: string, birthDate: string, hireDate: string, email: string}>
     */
    public function getReceptionistsByGarageId(int $garageId): array
    {
        $receptionists = $this->receptionistRepository->findByGarage($garageId);

        return Collection::make($receptionists)
            ->map(fn($receptionist) => [
                'id' => $receptionist->getId(),
                'lastName' => $receptionist->getLastName(),
                'firstName' => $receptionist->getFirstName(),
                'birthDate' => $receptionist->getBirthDate()->format('Y-m-d'),
                'hireDate' => $receptionist->getStartDate()?->format('Y-m-d'),
                'email' => $receptionist->getEmail(),
            ])
            ->values()
            ->toArray();
    }
}
