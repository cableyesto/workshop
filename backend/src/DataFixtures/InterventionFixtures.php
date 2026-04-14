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

            // Optional fields - 50% chance each
            if ($faker->boolean(50)) {
                $intervention->setClientRequest($faker->paragraph(3));
            }

            if ($faker->boolean(50)) {
                $intervention->setFinalNote($faker->paragraph(3));
            }

            $manager->persist($intervention);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CarFixtures::class];
    }
}
