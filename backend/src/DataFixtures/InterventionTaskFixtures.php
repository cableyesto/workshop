<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\InterventionTask;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InterventionTaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // For each of the 10 interventions
        for ($i = 1; $i <= 10; ++$i) {
            $intervention = $this->getReference(
                sprintf(InterventionFixtures::INTERVENTION_REFERENCE, $i),
                \App\Entity\Intervention::class
            );

            // Generate 1 to 4 random service tasks per intervention
            $taskCount = $faker->numberBetween(1, 4);
            $usedServices = [];

            for ($j = 0; $j < $taskCount; ++$j) {
                // Get random service (1 to 20 services available)
                $serviceIndex = $faker->numberBetween(1, 20);

                // Avoid duplicate services in same intervention
                if (!in_array($serviceIndex, $usedServices)) {
                    $serviceTask = $this->getReference(
                        sprintf(ServiceTaskFixtures::SERVICE_TASK_REFERENCE, $serviceIndex),
                        \App\Entity\ServiceTask::class
                    );

                    $interventionTask = new InterventionTask();
                    $interventionTask->setIntervention($intervention)
                        ->setServiceTask($serviceTask)
                        ->setQuantity($faker->numberBetween(1, 3)); // Quantity: 1-3

                    $manager->persist($interventionTask);
                    $usedServices[] = $serviceIndex;
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [InterventionFixtures::class, ServiceTaskFixtures::class];
    }
}
