<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Garage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClientFixtures extends Fixture implements DependentFixtureInterface
{
    public const CLIENT_REFERENCE = 'client_%d';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 10; ++$i) {
            $client = new Client();

            // Assign client to a random garage (1-5)
            $garageIndex = $faker->numberBetween(1, 5);
            $garage = $this->getReference(sprintf(GarageFixtures::GARAGE_REFERENCE, $garageIndex), Garage::class);

            $client->setLastName($faker->lastName())
                ->setFirstName($faker->firstName())
                ->setPhoneNumber($this->generateFrenchPhoneNumber($faker))
                ->setIsClientCalledBack($faker->boolean(40)) // 40% chance of being called back
                ->setGarage($garage);

            // 50% of clients have email, 50% don't
            if ($faker->boolean(50)) {
                $client->setEmail($faker->unique()->safeEmail());
            }

            $manager->persist($client);
            $this->addReference(sprintf(self::CLIENT_REFERENCE, $i), $client);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GarageFixtures::class,
        ];
    }

    private function generateFrenchPhoneNumber(\Faker\Generator $faker): string
    {
        // French phone number format: +33 X XX XX XX XX or 0X XX XX XX XX
        $formats = [
            '+33 # ## ## ## ##',     // International format
            '0# ## ## ## ##',        // Domestic format
        ];

        return $faker->numerify($faker->randomElement($formats));
    }
}
