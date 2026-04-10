<?php

namespace App\DataFixtures;

use App\Entity\Mechanic;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MechanicFixtures extends Fixture
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

            $manager->persist($mechanic);
        }

        $manager->flush();
    }
}
