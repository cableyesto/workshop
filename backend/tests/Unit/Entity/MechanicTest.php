<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Mechanic;
use PHPUnit\Framework\TestCase;

class MechanicTest extends TestCase
{
    private Mechanic $mechanic;

    protected function setUp(): void
    {
        $this->mechanic = new Mechanic();
    }

    /**
     * Test that a valid 4-digit PIN can be set and verified
     */
    public function testSetPinAndVerifyValidPin(): void
    {
        $validPin = '1234';

        $this->mechanic->setPin($validPin);

        $this->assertTrue($this->mechanic->verifyPin($validPin));
    }

    /**
     * Test that an incorrect PIN fails verification
     */
    public function testVerifyIncorrectPin(): void
    {
        $this->mechanic->setPin('1234');

        $this->assertFalse($this->mechanic->verifyPin('5678'));
    }

    /**
     * Test that isValidPin accepts exactly 4 digits
     */
    public function testIsValidPinWithValidFormats(): void
    {
        $this->assertTrue(Mechanic::isValidPin('0000'));
        $this->assertTrue(Mechanic::isValidPin('1234'));
        $this->assertTrue(Mechanic::isValidPin('9999'));
    }

    /**
     * Test that isValidPin rejects less than 4 digits
     */
    public function testIsValidPinRejectsLessThanFourDigits(): void
    {
        $this->assertFalse(Mechanic::isValidPin('123'));
        $this->assertFalse(Mechanic::isValidPin('12'));
        $this->assertFalse(Mechanic::isValidPin('1'));
        $this->assertFalse(Mechanic::isValidPin(''));
    }

    /**
     * Test that isValidPin rejects more than 4 digits
     */
    public function testIsValidPinRejectsMoreThanFourDigits(): void
    {
        $this->assertFalse(Mechanic::isValidPin('12345'));
        $this->assertFalse(Mechanic::isValidPin('123456'));
    }

    /**
     * Test that isValidPin rejects non-numeric characters
     */
    public function testIsValidPinRejectsNonNumeric(): void
    {
        $this->assertFalse(Mechanic::isValidPin('12a4'));
        $this->assertFalse(Mechanic::isValidPin('abcd'));
        $this->assertFalse(Mechanic::isValidPin('12 4'));
        $this->assertFalse(Mechanic::isValidPin('12-4'));
    }

    /**
     * Test that setPin throws exception for invalid PIN format
     */
    public function testSetPinThrowsExceptionForInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN must be exactly 4 digits.');

        $this->mechanic->setPin('123'); // Only 3 digits
    }

    /**
     * Test that setPin throws exception for non-numeric PIN
     */
    public function testSetPinThrowsExceptionForNonNumericPin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN must be exactly 4 digits.');

        $this->mechanic->setPin('abcd');
    }

    /**
     * Test that PIN is hashed (not stored in plain text)
     */
    public function testPinIsHashed(): void
    {
        $plainPin = '1234';
        $this->mechanic->setPin($plainPin);

        $storedPin = $this->mechanic->getPin();

        // The stored PIN should not be the plain text
        $this->assertNotEquals($plainPin, $storedPin);

        // But verification should still work
        $this->assertTrue($this->mechanic->verifyPin($plainPin));
    }

    /**
     * Test that different PINs have different hashes
     */
    public function testDifferentPinsProduceDifferentHashes(): void
    {
        $mechanic1 = new Mechanic();
        $mechanic2 = new Mechanic();

        $mechanic1->setPin('1234');
        $mechanic2->setPin('1234');

        // Even though same PIN, hashes should be different (due to password_hash salt)
        $this->assertNotEquals($mechanic1->getPin(), $mechanic2->getPin());

        // But both should verify correctly
        $this->assertTrue($mechanic1->verifyPin('1234'));
        $this->assertTrue($mechanic2->verifyPin('1234'));
    }

    /**
     * Test that interventions collection is empty by default
     */
    public function testGetInterventionsReturnsEmptyCollectionByDefault(): void
    {
        $interventions = $this->mechanic->getInterventions();
        $this->assertCount(0, $interventions);
    }

    /**
     * Test adding an intervention
     */
    public function testAddIntervention(): void
    {
        $intervention = $this->createStub(\App\Entity\Intervention::class);

        $this->mechanic->addIntervention($intervention);

        $this->assertCount(1, $this->mechanic->getInterventions());
        $this->assertTrue($this->mechanic->getInterventions()->contains($intervention));
    }

    /**
     * Test that adding same intervention twice does not duplicate
     */
    public function testAddInterventionDoesNotDuplicate(): void
    {
        $intervention = $this->createStub(\App\Entity\Intervention::class);

        $this->mechanic->addIntervention($intervention);
        $this->mechanic->addIntervention($intervention);

        $this->assertCount(1, $this->mechanic->getInterventions());
    }

    /**
     * Test removing an intervention
     */
    public function testRemoveIntervention(): void
    {
        $intervention = $this->createStub(\App\Entity\Intervention::class);

        $this->mechanic->addIntervention($intervention);
        $this->assertCount(1, $this->mechanic->getInterventions());

        $this->mechanic->removeIntervention($intervention);
        $this->assertCount(0, $this->mechanic->getInterventions());
    }
}
