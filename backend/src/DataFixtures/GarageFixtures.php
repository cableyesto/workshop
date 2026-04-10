<?php

namespace App\DataFixtures;

use App\Entity\Garage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class GarageFixtures extends Fixture
{
    public const GARAGE_REFERENCE = 'garage_%d';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 5; ++$i) {
            $garage = new Garage();

            $garage->setSiretNumber($faker->unique()->numerify('##############'))
                ->setName($faker->company())
                ->setStreet($faker->streetAddress())
                ->setCity($faker->city())
                ->setZipCode($faker->postcode())
                ->setPhone($faker->phoneNumber());

            $manager->persist($garage);
            $this->addReference(sprintf(self::GARAGE_REFERENCE, $i), $garage);
        }

        $manager->flush();
    }
}
