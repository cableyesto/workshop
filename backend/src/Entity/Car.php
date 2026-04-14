<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_LICENSE_PLATE', fields: ['licensePlate'])]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $manufacturer = null;

    #[ORM\Column(length: 50)]
    private ?string $model = null;

    #[ORM\Column(length: 15)]
    private ?string $licensePlate = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 1900, max: 2100)]
    private ?int $registrationYear = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 1, max: 12)]
    private ?int $registrationMonth = null;

    #[ORM\Column(nullable: true)]
    private ?int $mileage = null;

    #[ORM\Column]
    private ?bool $isStored = null;

    #[ORM\ManyToOne(inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Color $color = null;

    #[ORM\ManyToOne(inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getLicensePlate(): ?string
    {
        return $this->licensePlate;
    }

    public function setLicensePlate(string $licensePlate): static
    {
        $this->licensePlate = $licensePlate;

        return $this;
    }

    public function getRegistrationYear(): ?int
    {
        return $this->registrationYear;
    }

    public function setRegistrationYear(?int $registrationYear): static
    {
        $this->registrationYear = $registrationYear;

        return $this;
    }

    public function getRegistrationMonth(): ?int
    {
        return $this->registrationMonth;
    }

    public function setRegistrationMonth(?int $registrationMonth): static
    {
        $this->registrationMonth = $registrationMonth;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(?int $mileage): static
    {
        $this->mileage = $mileage;

        return $this;
    }

    public function isStored(): ?bool
    {
        return $this->isStored;
    }

    public function setIsStored(bool $isStored): static
    {
        $this->isStored = $isStored;

        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
