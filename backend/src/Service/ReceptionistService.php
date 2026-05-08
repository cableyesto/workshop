<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Receptionist;
use App\Repository\GarageRepository;
use App\Repository\ReceptionistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ReceptionistService
{
    public function __construct(
        private readonly ReceptionistRepository $receptionistRepository,
        private readonly GarageRepository $garageRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
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

    /**
     * Create a new receptionist in a specific garage
     *
     * @param array{lastName: string, firstName: string, birthDate: string, hireDate?: string, email: string, password: string} $data
     * @throws \InvalidArgumentException
     */
    public function createReceptionist(int $garageId, array $data): Receptionist
    {
        $garage = $this->garageRepository->find($garageId);
        if (!$garage) {
            throw new \InvalidArgumentException('Garage not found');
        }

        $receptionist = new Receptionist();
        $receptionist->setLastName($data['lastName']);
        $receptionist->setFirstName($data['firstName']);

        // Convert birthDate from Y-m-d string to DateTimeImmutable
        $birthDate = \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthDate']);
        if ($birthDate === false) {
            throw new \InvalidArgumentException('Invalid birthDate format. Expected Y-m-d');
        }
        $receptionist->setBirthDate($birthDate);

        // Convert hireDate if provided
        if (!empty($data['hireDate'])) {
            $hireDate = \DateTimeImmutable::createFromFormat('Y-m-d', $data['hireDate']);
            if ($hireDate === false) {
                throw new \InvalidArgumentException('Invalid hireDate format. Expected Y-m-d');
            }
            $receptionist->setStartDate($hireDate);
        }

        // Set email and password (password will be hashed in setPassword)
        $receptionist->setEmail($data['email']);
        $receptionist->setPassword($data['password']);

        $receptionist->addGarage($garage);

        // Validate entity
        $errors = $this->validator->validate($receptionist);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        $this->entityManager->persist($receptionist);
        $this->entityManager->flush();

        return $receptionist;
    }

    /**
     * Update an existing receptionist
     *
     * @param array{lastName?: string, firstName?: string, birthDate?: string, hireDate?: string, email?: string, password?: string} $data
     * @throws \InvalidArgumentException
     */
    public function updateReceptionist(int $receptionistId, int $garageId, array $data): Receptionist
    {
        $receptionist = $this->receptionistRepository->find($receptionistId);
        if (!$receptionist) {
            throw new \InvalidArgumentException('Receptionist not found');
        }

        // Verify receptionist belongs to the requesting user's garage
        $belongsToGarage = false;
        foreach ($receptionist->getGarages() as $garage) {
            if ($garage->getId() === $garageId) {
                $belongsToGarage = true;
                break;
            }
        }

        if (!$belongsToGarage) {
            throw new \InvalidArgumentException('Unauthorized: receptionist does not belong to your garage');
        }

        // Update personal information
        if (isset($data['lastName'])) {
            $receptionist->setLastName($data['lastName']);
        }

        if (isset($data['firstName'])) {
            $receptionist->setFirstName($data['firstName']);
        }

        if (isset($data['birthDate'])) {
            $birthDate = \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthDate']);
            if ($birthDate === false) {
                throw new \InvalidArgumentException('Invalid birthDate format. Expected Y-m-d');
            }
            $receptionist->setBirthDate($birthDate);
        }

        // Update hireDate
        if (isset($data['hireDate'])) {
            if (!empty($data['hireDate'])) {
                $hireDate = \DateTimeImmutable::createFromFormat('Y-m-d', $data['hireDate']);
                if ($hireDate === false) {
                    throw new \InvalidArgumentException('Invalid hireDate format. Expected Y-m-d');
                }
                $receptionist->setStartDate($hireDate);
            } else {
                $receptionist->setStartDate(null);
            }
        }

        // Update email
        if (isset($data['email'])) {
            $receptionist->setEmail($data['email']);
        }

        // Update password if provided (optional in edit mode)
        if (isset($data['password']) && !empty($data['password'])) {
            $receptionist->setPassword($data['password']);
        }

        // Validate entity
        $errors = $this->validator->validate($receptionist);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        $this->entityManager->flush();

        return $receptionist;
    }
}
