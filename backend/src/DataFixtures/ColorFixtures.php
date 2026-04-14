<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Color;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ColorFixtures extends Fixture
{
    public const COLOR_REFERENCE = 'color_%s';

    private array $colors = [
        ['Blanc', '#FFFFFF'],
        ['Noir', '#000000'],
        ['Gris', '#808080'],
        ['Argent', '#C0C0C0'],
        ['Bleu', '#0000FF'],
        ['Rouge', '#FF0000'],
        ['Beige', '#F5F5DC'],
        ['Marron', '#8B4513'],
        ['Vert', '#008000'],
        ['Jaune', '#FFFF00'],
        ['Or', '#FFD700'],
        ['Rose', '#FFC0CB'],
        ['Orange', '#FFA500'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->colors as $index => $colorData) {
            $color = new Color();
            $color->setName($colorData[0])
                ->setHexCode($colorData[1]);

            $manager->persist($color);
            $this->addReference(sprintf(self::COLOR_REFERENCE, $colorData[0]), $color);
        }

        $manager->flush();
    }
}
