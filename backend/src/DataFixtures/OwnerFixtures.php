<?php

namespace App\DataFixtures;

use App\Entity\Owner;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OwnerFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 2; ++$i) {
            $owner = new Owner();

            $plainPassword = 'password123';
            $hashedPassword = $this->passwordHasher->hashPassword($owner, $plainPassword);

            $owner->setEmail($faker->unique()->safeEmail())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPassword($hashedPassword);

            // Associate each owner with 1-3 random garages
            $garageCount = $faker->numberBetween(1, 3);
            $usedGarages = [];

            for ($j = 0; $j < $garageCount; ++$j) {
                $garageIndex = $faker->numberBetween(1, 5);

                // Avoid duplicate garage associations
                if (!in_array($garageIndex, $usedGarages)) {
                    $garage = $this->getReference(
                        sprintf(GarageFixtures::GARAGE_REFERENCE, $garageIndex),
                        \App\Entity\Garage::class
                    );
                    $owner->addGarage($garage);
                    $usedGarages[] = $garageIndex;
                }
            }

            $manager->persist($owner);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [GarageFixtures::class];
    }
}
