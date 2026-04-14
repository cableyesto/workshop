<?php

namespace App\Entity;

use App\Enum\DocumentType;
use App\Enum\InterventionStatus;
use App\Enum\InterventionType;
use App\Repository\InterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(enumType: InterventionStatus::class)]
    private ?InterventionStatus $status = null;

    #[ORM\Column(enumType: InterventionType::class)]
    private ?InterventionType $type = null;

    #[ORM\Column(enumType: DocumentType::class)]
    private ?DocumentType $documentType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $clientRequest = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $finalNote = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Car $car = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getStatus(): ?InterventionStatus
    {
        return $this->status;
    }

    public function setStatus(InterventionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): ?InterventionType
    {
        return $this->type;
    }

    public function setType(InterventionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDocumentType(): ?DocumentType
    {
        return $this->documentType;
    }

    public function setDocumentType(DocumentType $documentType): static
    {
        $this->documentType = $documentType;

        return $this;
    }

    public function getClientRequest(): ?string
    {
        return $this->clientRequest;
    }

    public function setClientRequest(?string $clientRequest): static
    {
        $this->clientRequest = $clientRequest;

        return $this;
    }

    public function getFinalNote(): ?string
    {
        return $this->finalNote;
    }

    public function setFinalNote(?string $finalNote): static
    {
        $this->finalNote = $finalNote;

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): static
    {
        $this->car = $car;

        return $this;
    }
}
