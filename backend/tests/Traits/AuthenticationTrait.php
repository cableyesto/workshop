<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use App\Tests\Fixtures\SecurityFixtures;

/**
 * Authentication helpers for integration tests
 *
 * Provides convenient methods to get JWT tokens for different user types
 * Uses credentials from SecurityFixtures
 */
trait AuthenticationTrait
{
    /**
     * Get valid JWT token for Owner (garage 1)
     *
     * @return string JWT token
     */
    protected function getOwnerToken(): string
    {
        return $this->getJwtToken(
            SecurityFixtures::OWNER_EMAIL,
            SecurityFixtures::OWNER_PASSWORD
        );
    }

    /**
     * Get valid JWT token for second Owner (garage 2)
     *
     * @return string JWT token
     */
    protected function getOwner2Token(): string
    {
        return $this->getJwtToken(
            SecurityFixtures::OWNER2_EMAIL,
            SecurityFixtures::OWNER2_PASSWORD
        );
    }

    /**
     * Get valid JWT token for Receptionist (garage 1)
     *
     * @return string JWT token
     */
    protected function getReceptionistToken(): string
    {
        return $this->getJwtToken(
            SecurityFixtures::RECEPTIONIST_EMAIL,
            SecurityFixtures::RECEPTIONIST_PASSWORD,
            SecurityFixtures::GARAGE_SIRET
        );
    }

    /**
     * Get valid JWT token for second Receptionist (garage 2)
     *
     * @return string JWT token
     */
    protected function getReceptionist2Token(): string
    {
        return $this->getJwtToken(
            SecurityFixtures::RECEPTIONIST2_EMAIL,
            SecurityFixtures::RECEPTIONIST2_PASSWORD,
            SecurityFixtures::GARAGE2_SIRET
        );
    }

    /**
     * Get valid JWT token for Mechanic (garage 1)
     *
     * @return string JWT token
     */
    protected function getMechanicToken(): string
    {
        return $this->getMechanicJwtToken(
            SecurityFixtures::GARAGE_SIRET,
            SecurityFixtures::MECHANIC_PIN
        );
    }

    /**
     * Get valid JWT token for second Mechanic (garage 2)
     *
     * @return string JWT token
     */
    protected function getMechanic2Token(): string
    {
        return $this->getMechanicJwtToken(
            SecurityFixtures::GARAGE2_SIRET,
            '5678' // Second mechanic's PIN
        );
    }

    /**
     * Create authenticated client for Owner
     *
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function createOwnerClient()
    {
        return $this->createAuthenticatedClient($this->getOwnerToken());
    }

    /**
     * Create authenticated client for Receptionist
     *
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function createReceptionistClient()
    {
        return $this->createAuthenticatedClient($this->getReceptionistToken());
    }

    /**
     * Create authenticated client for Mechanic
     *
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function createMechanicClient()
    {
        return $this->createAuthenticatedClient($this->getMechanicToken());
    }

    /**
     * Create authenticated client for second Owner (cross-garage testing)
     *
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function createOwner2Client()
    {
        return $this->createAuthenticatedClient($this->getOwner2Token());
    }

    /**
     * Create authenticated client for second Receptionist (cross-garage testing)
     *
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function createReceptionist2Client()
    {
        return $this->createAuthenticatedClient($this->getReceptionist2Token());
    }

    /**
     * Create authenticated client for second Mechanic (cross-garage testing)
     *
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function createMechanic2Client()
    {
        return $this->createAuthenticatedClient($this->getMechanic2Token());
    }

    /**
     * These methods must be implemented by the test class using this trait
     * (typically provided by ApiTestCase)
     */
    abstract protected function getJwtToken(string $email, string $password, ?string $siret = null): string;
    abstract protected function getMechanicJwtToken(string $siret, string $pin): string;
    abstract protected function createAuthenticatedClient(string $token);
}
