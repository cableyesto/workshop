<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Mechanic;
use App\Repository\GarageRepository;
use App\Repository\MechanicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MechanicService
{
    public function __construct(
        private readonly MechanicRepository $mechanicRepository,
        private readonly GarageRepository $garageRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
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

    /**
     * Validate that a PIN is unique within a garage
     *
     * @throws \InvalidArgumentException
     */
    private function validatePinUniqueness(string $pin, int $garageId, ?int $excludeMechanicId = null): void
    {
        // Validate PIN format
        if (!\App\Entity\Mechanic::isValidPin($pin)) {
            throw new \InvalidArgumentException('PIN must be exactly 4 digits');
        }

        // Get all mechanics in the garage
        $mechanics = $this->mechanicRepository->findByGarage($garageId);

        foreach ($mechanics as $mechanic) {
            // Skip the mechanic being updated (if applicable)
            if ($excludeMechanicId !== null && $mechanic->getId() === $excludeMechanicId) {
                continue;
            }

            // Check if PIN is already used
            if ($mechanic->verifyPin($pin)) {
                throw new \InvalidArgumentException('PIN already in use in this garage');
            }
        }
    }

    /**
     * Create a new mechanic in a specific garage
     *
     * @param array{lastName: string, firstName: string, birthDate: string, hireDate?: string, pin: string} $data
     * @throws \InvalidArgumentException
     */
    public function createMechanic(int $garageId, array $data): Mechanic
    {
        $garage = $this->garageRepository->find($garageId);
        if (!$garage) {
            throw new \InvalidArgumentException('Garage not found');
        }

        // Validate PIN uniqueness
        $this->validatePinUniqueness($data['pin'], $garageId);

        $mechanic = new Mechanic();
        $mechanic->setLastName($data['lastName']);
        $mechanic->setFirstName($data['firstName']);

        // Convert birthDate from Y-m-d string to DateTimeImmutable
        $birthDate = \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthDate']);
        if ($birthDate === false) {
            throw new \InvalidArgumentException('Invalid birthDate format. Expected Y-m-d');
        }
        $mechanic->setBirthDate($birthDate);

        // Convert hireDate if provided
        if (!empty($data['hireDate'])) {
            $hireDate = \DateTimeImmutable::createFromFormat('Y-m-d', $data['hireDate']);
            if ($hireDate === false) {
                throw new \InvalidArgumentException('Invalid hireDate format. Expected Y-m-d');
            }
            $mechanic->setStartDate($hireDate);
        }

        // Set PIN (will be hashed by the entity)
        $mechanic->setPin($data['pin']);

        $mechanic->addGarage($garage);

        // Validate entity
        $errors = $this->validator->validate($mechanic);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        $this->entityManager->persist($mechanic);
        $this->entityManager->flush();

        return $mechanic;
    }
}
