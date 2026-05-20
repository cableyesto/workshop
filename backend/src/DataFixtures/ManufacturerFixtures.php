<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Manufacturer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ManufacturerFixtures extends Fixture
{
    public const MANUFACTURER_REFERENCE = 'manufacturer_%s';

    private array $manufacturers = [
        // French
        'Peugeot',
        'Renault',
        'Citroën',
        'Alpine',
        'DS Automobiles',
        'Bugatti',

        // German
        'Volkswagen',
        'Audi',
        'BMW',
        'Mercedes-Benz',
        'Porsche',
        'Opel',
        'Smart',

        // Japanese
        'Toyota',
        'Honda',
        'Nissan',
        'Mazda',
        'Mitsubishi',
        'Suzuki',
        'Subaru',
        'Lexus',
        'Infiniti',

        // Korean
        'Hyundai',
        'Kia',
        'Genesis',

        // American
        'Ford',
        'Chevrolet',
        'Jeep',
        'Tesla',
        'Cadillac',
        'Dodge',
        'Chrysler',

        // Italian
        'Fiat',
        'Alfa Romeo',
        'Ferrari',
        'Lamborghini',
        'Maserati',
        'Lancia',

        // British
        'Jaguar',
        'Land Rover',
        'Mini',
        'Aston Martin',
        'Bentley',
        'Rolls-Royce',

        // Swedish
        'Volvo',

        // Czech
        'Škoda',

        // Spanish
        'SEAT',
        'Cupra',

        // Chinese
        'BYD',
        'Geely',
        'MG',

        // Other European
        'Dacia',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->manufacturers as $manufacturerName) {
            $manufacturer = new Manufacturer();
            $manufacturer->setName($manufacturerName);

            $manager->persist($manufacturer);
            $this->addReference(
                sprintf(self::MANUFACTURER_REFERENCE, $manufacturerName),
                $manufacturer
            );
        }

        $manager->flush();
    }
}
