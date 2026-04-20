<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Mechanic;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MechanicFixtures extends Fixture implements DependentFixtureInterface
{
    public const MECHANIC_REFERENCE = 'mechanic_%d';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Predefined PINs for testing (easy to remember and test)
        $pins = ['1234', '5678', '9012', '3456', '7890'];

        for ($i = 1; $i <= 5; ++$i) {
            $mechanic = new Mechanic();

            // Employee fields (parent)
            $mechanic->setLastName($faker->lastName())
                ->setFirstName($faker->firstName())
                ->setBirthDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-50 years', '-20 years')))
                ->setStartDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-5 years', 'now')));

            // Mechanic field - Use predefined PIN from array
            $pin = $pins[$i - 1]; // $i starts at 1, array starts at 0
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
            $this->addReference(sprintf(self::MECHANIC_REFERENCE, $i), $mechanic);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [GarageFixtures::class];
    }
}
