<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Intervention;
use App\Enum\InterventionStatus;
use App\Enum\InterventionType;
use App\Enum\DocumentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InterventionFixtures extends Fixture implements DependentFixtureInterface
{
    public const INTERVENTION_REFERENCE = 'intervention_%d';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 10; ++$i) {
            $intervention = new Intervention();

            // Get random car (1 to 10)
            $carIndex = $faker->numberBetween(1, 10);
            $car = $this->getReference(
                sprintf(CarFixtures::CAR_REFERENCE, $carIndex),
                \App\Entity\Car::class
            );

            // Required fields
            $date = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-30 days', 'now')
            );
            $startTime = \DateTimeImmutable::createFromMutable(
                $faker->dateTime()
            );

            $intervention->setDate($date)
                ->setStartTime($startTime)
                ->setStatus($faker->randomElement(InterventionStatus::cases()))
                ->setType($faker->randomElement(InterventionType::cases()))
                ->setDocumentType($faker->randomElement(DocumentType::cases()))
                ->setCar($car);

            // Find a mechanic that belongs to the same garage as the car's client
            $carGarage = $car->getClient()->getGarage();
            $validMechanic = null;

            // Try all 5 mechanics to find one that works at this garage
            $mechanicIndices = range(1, 5);
            shuffle($mechanicIndices);

            foreach ($mechanicIndices as $mechanicIndex) {
                $mechanic = $this->getReference(
                    sprintf(MechanicFixtures::MECHANIC_REFERENCE, $mechanicIndex),
                    \App\Entity\Mechanic::class
                );

                // Check if mechanic works at this garage
                foreach ($mechanic->getGarages() as $garage) {
                    if ($garage->getId() === $carGarage->getId()) {
                        $validMechanic = $mechanic;
                        break 2;
                    }
                }
            }

            // If no valid mechanic found, add the car's garage to the first mechanic
            if ($validMechanic === null) {
                $mechanic = $this->getReference(
                    sprintf(MechanicFixtures::MECHANIC_REFERENCE, 1),
                    \App\Entity\Mechanic::class
                );
                $mechanic->addGarage($carGarage);
                $validMechanic = $mechanic;
            }

            $intervention->addMechanic($validMechanic);

            // Optional fields - 50% chance each
            if ($faker->boolean(50)) {
                $intervention->setClientRequest($faker->paragraph(3));
            }

            if ($faker->boolean(50)) {
                $intervention->setFinalNote($faker->paragraph(3));
            }

            $manager->persist($intervention);
            $this->addReference(sprintf(self::INTERVENTION_REFERENCE, $i), $intervention);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CarFixtures::class, MechanicFixtures::class];
    }
}
