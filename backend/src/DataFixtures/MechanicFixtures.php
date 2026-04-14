<?php

namespace App\DataFixtures;

use App\Entity\Mechanic;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MechanicFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 5; ++$i) {
            $mechanic = new Mechanic();

            // Employee fields (parent)
            $mechanic->setLastName($faker->lastName())
                ->setFirstName($faker->firstName())
                ->setBirthDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-50 years', '-20 years')))
                ->setStartDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-5 years', 'now')));

            // Mechanic field - 4-digit PIN (validated and hashed in setPin)
            $pin = $faker->numerify('####');
            $mechanic->setPin($pin);

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
                    $mechanic->addGarage($garage);
                    $usedGarages[] = $garageIndex;
                }
            }

            $manager->persist($mechanic);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [GarageFixtures::class];
    }
}
