<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Garage;
use App\Entity\Mechanic;
use App\Entity\Owner;
use App\Entity\Receptionist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Security test fixtures
 *
 * Loads minimal data required for authentication and authorization tests:
 * - Test garage with known SIRET
 * - Owner with known credentials
 * - Receptionist with known credentials
 * - Mechanic with known PIN
 */
class SecurityFixtures extends Fixture
{
    public const GARAGE_SIRET = '12345678901234';
    public const GARAGE_NAME = 'Test Garage';

    public const OWNER_EMAIL = 'owner@test.com';
    public const OWNER_PASSWORD = 'password123';

    public const RECEPTIONIST_EMAIL = 'receptionist@test.com';
    public const RECEPTIONIST_PASSWORD = 'password123';

    public const MECHANIC_PIN = '1234';
    public const MECHANIC_FIRSTNAME = 'John';
    public const MECHANIC_LASTNAME = 'Doe';

    // Second garage for cross-garage access tests
    public const GARAGE2_SIRET = '98765432109876';
    public const GARAGE2_NAME = 'Second Test Garage';

    public const OWNER2_EMAIL = 'owner2@test.com';
    public const OWNER2_PASSWORD = 'password123';

    public const RECEPTIONIST2_EMAIL = 'receptionist2@test.com';
    public const RECEPTIONIST2_PASSWORD = 'password123';

    public function load(ObjectManager $manager): void
    {
        // Create first garage
        $garage1 = new Garage();
        $garage1->setSiretNumber(self::GARAGE_SIRET);
        $garage1->setName(self::GARAGE_NAME);
        $garage1->setStreet('123 Test Street');
        $garage1->setCity('Test City');
        $garage1->setZipCode('75001');
        $garage1->setPhone('0123456789');
        $manager->persist($garage1);

        // Create second garage for cross-garage tests
        $garage2 = new Garage();
        $garage2->setSiretNumber(self::GARAGE2_SIRET);
        $garage2->setName(self::GARAGE2_NAME);
        $garage2->setStreet('456 Other Street');
        $garage2->setCity('Other City');
        $garage2->setZipCode('75002');
        $garage2->setPhone('0987654321');
        $manager->persist($garage2);

        // Create Owner 1 (owns garage1)
        $owner1 = new Owner();
        $owner1->setEmail(self::OWNER_EMAIL);
        $owner1->setFirstName('Owner');
        $owner1->setLastName('Test');
        // Use password_hash directly - Symfony recognizes PASSWORD_BCRYPT during authentication
        $owner1->setPassword(password_hash(self::OWNER_PASSWORD, PASSWORD_BCRYPT));
        $owner1->addGarage($garage1);
        $manager->persist($owner1);

        // Create Owner 2 (owns garage2)
        $owner2 = new Owner();
        $owner2->setEmail(self::OWNER2_EMAIL);
        $owner2->setFirstName('Owner2');
        $owner2->setLastName('Test');
        // Use password_hash directly - Symfony recognizes PASSWORD_BCRYPT during authentication
        $owner2->setPassword(password_hash(self::OWNER2_PASSWORD, PASSWORD_BCRYPT));
        $owner2->addGarage($garage2);
        $manager->persist($owner2);

        // Create Receptionist 1 (works at garage1)
        $receptionist1 = new Receptionist();
        $receptionist1->setEmail(self::RECEPTIONIST_EMAIL);
        $receptionist1->setFirstName('Receptionist');
        $receptionist1->setLastName('Test');
        $receptionist1->setBirthDate(new \DateTimeImmutable('1988-03-15'));
        $receptionist1->setStartDate(new \DateTimeImmutable('2019-01-10'));
        // Receptionist hashes password internally via setPassword()
        $receptionist1->setPassword(self::RECEPTIONIST_PASSWORD);
        $receptionist1->addGarage($garage1); // Employee has ManyToMany with Garage
        $manager->persist($receptionist1);

        // Create Receptionist 2 (works at garage2)
        $receptionist2 = new Receptionist();
        $receptionist2->setEmail(self::RECEPTIONIST2_EMAIL);
        $receptionist2->setFirstName('Receptionist2');
        $receptionist2->setLastName('Test');
        $receptionist2->setBirthDate(new \DateTimeImmutable('1990-07-20'));
        $receptionist2->setStartDate(new \DateTimeImmutable('2020-03-01'));
        $receptionist2->setPassword(self::RECEPTIONIST2_PASSWORD);
        $receptionist2->addGarage($garage2); // Employee has ManyToMany with Garage
        $manager->persist($receptionist2);

        // Create Mechanic 1 (works at garage1)
        $mechanic1 = new Mechanic();
        $mechanic1->setFirstName(self::MECHANIC_FIRSTNAME);
        $mechanic1->setLastName(self::MECHANIC_LASTNAME);
        $mechanic1->setPin(self::MECHANIC_PIN);
        $mechanic1->setBirthDate(new \DateTimeImmutable('1990-01-01'));
        $mechanic1->setStartDate(new \DateTimeImmutable('2020-01-01'));
        $garage1->addEmployee($mechanic1);
        $manager->persist($mechanic1);

        // Create Mechanic 2 (works at garage2)
        $mechanic2 = new Mechanic();
        $mechanic2->setFirstName('Jane');
        $mechanic2->setLastName('Smith');
        $mechanic2->setPin('5678');
        $mechanic2->setBirthDate(new \DateTimeImmutable('1992-05-15'));
        $mechanic2->setStartDate(new \DateTimeImmutable('2021-06-01'));
        $garage2->addEmployee($mechanic2);
        $manager->persist($mechanic2);

        $manager->flush();
    }
}
