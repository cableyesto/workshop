<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ClientRepository;

final class ClientService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
    ) {
    }

    /**
     * Update client information
     */
    public function updateClient(
        int $clientId,
        string $firstName,
        string $lastName,
        ?string $email,
        string $phoneNumber,
    ): void {
        $client = $this->clientRepository->find($clientId);

        if (!$client) {
            throw new \RuntimeException('Client not found');
        }

        $client
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPhoneNumber($phoneNumber);

        $this->clientRepository->getEntityManager()->flush();
    }
}
