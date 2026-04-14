<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MechanicRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MechanicRepository::class)]
class Mechanic extends Employee
{
    #[ORM\Column(length: 255)]
    private ?string $pin = null;

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
}
