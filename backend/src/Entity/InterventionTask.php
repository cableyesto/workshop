<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InterventionTaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionTaskRepository::class)]
#[ORM\Table(name: 'intervention_task')]
class InterventionTask
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'interventionTasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Intervention $intervention = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'interventionTasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceTask $serviceTask = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1])]
    private int $quantity = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): static
    {
        $this->intervention = $intervention;

        return $this;
    }

    public function getServiceTask(): ?ServiceTask
    {
        return $this->serviceTask;
    }

    public function setServiceTask(?ServiceTask $serviceTask): static
    {
        $this->serviceTask = $serviceTask;

        return $this;
    }
}
