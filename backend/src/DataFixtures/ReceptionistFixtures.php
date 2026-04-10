<?php

namespace App\DataFixtures;

use App\Entity\Receptionist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ReceptionistFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 5; ++$i) {
            $receptionist = new Receptionist();

            $plainPassword = 'password123';
            $hashedPassword = $this->passwordHasher->hashPassword($receptionist, $plainPassword);

            // Employee fields (parent)
            $receptionist->setLastName($faker->lastName())
                ->setFirstName($faker->firstName())
                ->setBirthDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-50 years', '-20 years')))
                ->setStartDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-5 years', 'now')));

            // Receptionist fields
            $receptionist->setEmail($faker->unique()->companyEmail())
                ->setPassword($hashedPassword);

            $manager->persist($receptionist);
        }

        $manager->flush();
    }
}
