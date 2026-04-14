<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Car;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CarFixtures extends Fixture implements DependentFixtureInterface
{
    private array $colorNames = [
        'Blanc', 'Noir', 'Gris', 'Argent', 'Bleu',
        'Rouge', 'Beige', 'Marron', 'Vert', 'Jaune',
        'Or', 'Rose', 'Orange',
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Popular French car manufacturers and models
        $carData = [
            ['Peugeot', ['208', '308', '3008', '5008']],
            ['Renault', ['Clio', 'Megane', 'Captur', 'Kadjar']],
            ['Citroën', ['C3', 'C4', 'C5 Aircross', 'Berlingo']],
            ['Volkswagen', ['Golf', 'Polo', 'Tiguan', 'Passat']],
            ['Toyota', ['Yaris', 'Corolla', 'RAV4', 'C-HR']],
        ];

        // Create 5 cars WITH all fields (including optional)
        for ($i = 1; $i <= 5; ++$i) {
            $car = new Car();
            $carInfo = $faker->randomElement($carData);
            $colorName = $faker->randomElement($this->colorNames);
            $clientIndex = $faker->numberBetween(1, 10);

            $color = $this->getReference(
                sprintf(ColorFixtures::COLOR_REFERENCE, $colorName),
                \App\Entity\Color::class
            );

            $client = $this->getReference(
                sprintf(ClientFixtures::CLIENT_REFERENCE, $clientIndex),
                \App\Entity\Client::class
            );

            $car->setManufacturer($carInfo[0])
                ->setModel($faker->randomElement($carInfo[1]))
                ->setLicensePlate($this->generateFrenchLicensePlate($faker))
                ->setColor($color)
                ->setClient($client)
                ->setRegistrationYear($faker->numberBetween(2015, 2024))
                ->setRegistrationMonth($faker->numberBetween(1, 12))
                ->setMileage($faker->numberBetween(5000, 150000))
                ->setIsStored($faker->boolean(30)); // 30% chance of being stored

            $manager->persist($car);
        }

        // Create 5 cars WITHOUT optional fields
        for ($i = 1; $i <= 5; ++$i) {
            $car = new Car();
            $carInfo = $faker->randomElement($carData);
            $colorName = $faker->randomElement($this->colorNames);
            $clientIndex = $faker->numberBetween(1, 10);

            $color = $this->getReference(
                sprintf(ColorFixtures::COLOR_REFERENCE, $colorName),
                \App\Entity\Color::class
            );

            $client = $this->getReference(
                sprintf(ClientFixtures::CLIENT_REFERENCE, $clientIndex),
                \App\Entity\Client::class
            );

            $car->setManufacturer($carInfo[0])
                ->setModel($faker->randomElement($carInfo[1]))
                ->setLicensePlate($this->generateFrenchLicensePlate($faker))
                ->setColor($color)
                ->setClient($client)
                ->setIsStored($faker->boolean(20)); // 20% chance of being stored

            // Optional fields remain null
            $manager->persist($car);
        }

        $manager->flush();
    }

    private function generateFrenchLicensePlate(\Faker\Generator $faker): string
    {
        // French license plate format: AA-123-BB
        return sprintf(
            '%s-%s-%s',
            strtoupper($faker->lexify('??')),
            $faker->numerify('###'),
            strtoupper($faker->lexify('??'))
        );
    }

    public function getDependencies(): array
    {
        return [ColorFixtures::class, ClientFixtures::class];
    }
}
