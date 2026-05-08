<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Receptionist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReceptionistFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct()
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 5; ++$i) {
            $receptionist = new Receptionist();

            // Employee fields (parent)
            $receptionist->setLastName($faker->lastName())
                ->setFirstName($faker->firstName())
                ->setBirthDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-50 years', '-20 years')))
                ->setStartDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-5 years', 'now')));

            // Receptionist fields (password will be hashed in setPassword)
            $receptionist->setEmail($faker->unique()->companyEmail())
                ->setPassword('password123');

            // Associate with 1-2 garages (employee must have at least 1)
            $garageCount = $faker->numberBetween(1, 2);
            $usedGarages = [];

            for ($j = 0; $j < $garageCount; ++$j) {
                $garageIndex = $faker->numberBetween(1, 5);

                if (!in_array($garageIndex, $usedGarages)) {
                    $garage = $this->getReference(
                        sprintf(GarageFixtures::GARAGE_REFERENCE, $garageIndex),
                        \App\Entity\Garage::class
                    );
                    $receptionist->addGarage($garage);
                    $usedGarages[] = $garageIndex;
                }
            }

            $manager->persist($receptionist);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [GarageFixtures::class];
    }
}
