<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ServiceTaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceTaskRepository::class)]
class ServiceTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    /**
     * @var Collection<int, InterventionTask>
     */
    #[ORM\OneToMany(targetEntity: InterventionTask::class, mappedBy: 'serviceTask')]
    #[Assert\Count(min: 1, minMessage: 'A service task must consists of at least one intervention task.')]
    private Collection $interventionTasks;

    public function __construct()
    {
        $this->interventionTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * @return Collection<int, InterventionTask>
     */
    public function getInterventionTasks(): Collection
    {
        return $this->interventionTasks;
    }

    public function addInterventionTask(InterventionTask $interventionTask): static
    {
        if (!$this->interventionTasks->contains($interventionTask)) {
            $this->interventionTasks->add($interventionTask);
            $interventionTask->setServiceTask($this);
        }

        return $this;
    }

    public function removeInterventionTask(InterventionTask $interventionTask): static
    {
        if ($this->interventionTasks->removeElement($interventionTask)) {
            // set the owning side to null (unless already changed)
            if ($interventionTask->getServiceTask() === $this) {
                $interventionTask->setServiceTask(null);
            }
        }

        return $this;
    }
}
