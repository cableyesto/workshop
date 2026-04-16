<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ServiceTask;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ServiceTaskFixtures extends Fixture
{
    public const SERVICE_TASK_REFERENCE = 'service_task_%d';

    private array $services = [
        ['Vidange moteur', '45.00'],
        ['Changement filtre à huile', '15.00'],
        ['Changement filtre à air', '20.00'],
        ['Remplacement plaquettes de frein avant', '80.00'],
        ['Remplacement plaquettes de frein arrière', '70.00'],
        ['Remplacement disques de frein avant', '120.00'],
        ['Remplacement disques de frein arrière', '110.00'],
        ['Contrôle technique', '75.00'],
        ['Diagnostic électronique', '60.00'],
        ['Changement batterie', '90.00'],
        ['Remplacement pneu', '65.00'],
        ['Équilibrage roues', '25.00'],
        ['Parallélisme', '70.00'],
        ['Changement courroie de distribution', '350.00'],
        ['Remplacement amortisseurs avant', '200.00'],
        ['Remplacement amortisseurs arrière', '180.00'],
        ['Vidange boîte de vitesses', '85.00'],
        ['Recharge climatisation', '95.00'],
        ['Remplacement pare-brise', '250.00'],
        ['Changement balais d\'essuie-glace', '12.00'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->services as $index => $serviceData) {
            $serviceTask = new ServiceTask();
            $serviceTask->setName($serviceData[0])
                ->setUnitPrice($serviceData[1]);

            $manager->persist($serviceTask);
            $this->addReference(sprintf(self::SERVICE_TASK_REFERENCE, $index + 1), $serviceTask);
        }

        $manager->flush();
    }
}
