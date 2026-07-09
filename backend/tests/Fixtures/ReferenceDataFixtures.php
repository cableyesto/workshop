<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Color;
use App\Entity\Manufacturer;
use App\Entity\ServiceTask;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Reference data fixtures for tests
 *
 * Loads basic reference data (colors, manufacturers, service tasks)
 * needed for controller tests.
 *
 * Mirrors production fixtures structure but with reduced dataset.
 */
class ReferenceDataFixtures extends Fixture
{
    // Reference constants for tests
    public const COLOR_BLANC = 'Blanc';
    public const COLOR_NOIR = 'Noir';
    public const COLOR_ROUGE = 'Rouge';

    public const MANUFACTURER_TOYOTA = 'Toyota';
    public const MANUFACTURER_RENAULT = 'Renault';
    public const MANUFACTURER_PEUGEOT = 'Peugeot';

    public const SERVICE_VIDANGE = 'Vidange moteur';
    public const SERVICE_FREINS = 'Remplacement plaquettes de frein avant';

    private array $colors = [
        ['Blanc', '#FFFFFF'],
        ['Noir', '#000000'],
        ['Gris', '#808080'],
        ['Bleu', '#0000FF'],
        ['Rouge', '#FF0000'],
        ['Vert', '#008000'],
    ];

    private array $manufacturers = [
        'Peugeot',
        'Renault',
        'Citroën',
        'Toyota',
        'Volkswagen',
        'Ford',
        'BMW',
    ];

    private array $services = [
        ['Vidange moteur', '45.00'],
        ['Changement filtre à huile', '15.00'],
        ['Remplacement plaquettes de frein avant', '80.00'],
        ['Contrôle technique', '75.00'],
        ['Diagnostic électronique', '60.00'],
    ];

    public function load(ObjectManager $manager): void
    {
        $this->loadColors($manager);
        $this->loadManufacturers($manager);
        $this->loadServiceTasks($manager);

        $manager->flush();
    }

    private function loadColors(ObjectManager $manager): void
    {
        foreach ($this->colors as $colorData) {
            $color = new Color();
            $color->setName($colorData[0])
                ->setHexCode($colorData[1]);

            $manager->persist($color);
        }
    }

    private function loadManufacturers(ObjectManager $manager): void
    {
        foreach ($this->manufacturers as $manufacturerName) {
            $manufacturer = new Manufacturer();
            $manufacturer->setName($manufacturerName);

            $manager->persist($manufacturer);
        }
    }

    private function loadServiceTasks(ObjectManager $manager): void
    {
        foreach ($this->services as $serviceData) {
            $serviceTask = new ServiceTask();
            $serviceTask->setName($serviceData[0])
                ->setUnitPrice($serviceData[1]);

            $manager->persist($serviceTask);
        }
    }
}
