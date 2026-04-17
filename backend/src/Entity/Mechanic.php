<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MechanicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: MechanicRepository::class)]
class Mechanic extends Employee implements UserInterface
{
    #[ORM\Column(length: 255)]
    private ?string $pin = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\ManyToMany(targetEntity: Intervention::class, inversedBy: 'mechanics')]
    private Collection $interventions;

    public function __construct()
    {
        parent::__construct();
        $this->interventions = new ArrayCollection();
    }

    public function getPin(): ?string
    {
        return $this->pin;
    }

    public function setPin(string $pin): static
    {
        if (!self::isValidPin($pin)) {
            throw new \InvalidArgumentException('PIN must be exactly 4 digits.');
        }

        $this->pin = password_hash($pin, PASSWORD_DEFAULT);

        return $this;
    }

    public function verifyPin(string $pin): bool
    {
        return password_verify($pin, $this->pin);
    }

    public static function isValidPin(string $pin): bool
    {
        return preg_match('/^\d{4}$/', $pin) === 1;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        $this->interventions->removeElement($intervention);

        return $this;
    }

    /**
     * Returns the identifier for this user (used by Symfony security).
     * For Mechanic, we use the ID as the identifier.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->getId();
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return ['ROLE_MECHANIC'];
    }

    /**
     * This method can be used to erase sensitive data from the user object.
     * We don't store plain-text credentials, so nothing to erase.
     */
    public function eraseCredentials(): void
    {
        // Nothing to erase - PIN is already hashed
    }
}
