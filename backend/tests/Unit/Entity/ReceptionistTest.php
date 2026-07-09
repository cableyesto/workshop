<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Receptionist;
use PHPUnit\Framework\TestCase;

class ReceptionistTest extends TestCase
{
    private Receptionist $receptionist;

    protected function setUp(): void
    {
        $this->receptionist = new Receptionist();
    }

    /**
     * Test that setPassword() hashes the password internally
     */
    public function testSetPasswordHashesPasswordInternally(): void
    {
        $plainPassword = 'myPlainPassword123';

        $this->receptionist->setPassword($plainPassword);
        $storedPassword = $this->receptionist->getPassword();

        // The stored password should NOT be the plain text
        $this->assertNotEquals($plainPassword, $storedPassword);

        // The stored password should be a hash (bcrypt starts with $2y$)
        $this->assertStringStartsWith('$2y$', $storedPassword);
    }

    /**
     * Test that password can be verified after being set
     */
    public function testPasswordCanBeVerifiedAfterBeingSet(): void
    {
        $plainPassword = 'testPassword456';

        $this->receptionist->setPassword($plainPassword);
        $storedPassword = $this->receptionist->getPassword();

        // Verify the plain password matches the hash
        $this->assertTrue(password_verify($plainPassword, $storedPassword));
    }

    /**
     * Test that wrong password fails verification
     */
    public function testWrongPasswordFailsVerification(): void
    {
        $this->receptionist->setPassword('correctPassword');
        $storedPassword = $this->receptionist->getPassword();

        // Wrong password should not verify
        $this->assertFalse(password_verify('wrongPassword', $storedPassword));
    }

    /**
     * Test that different calls with same password produce different hashes
     * This is due to password_hash using random salt
     */
    public function testSamePasswordProducesDifferentHashesOnDifferentInstances(): void
    {
        $plainPassword = 'samePassword';

        $receptionist1 = new Receptionist();
        $receptionist2 = new Receptionist();

        $receptionist1->setPassword($plainPassword);
        $receptionist2->setPassword($plainPassword);

        $hash1 = $receptionist1->getPassword();
        $hash2 = $receptionist2->getPassword();

        // Hashes should be different (random salt)
        $this->assertNotEquals($hash1, $hash2);

        // But both should verify correctly
        $this->assertTrue(password_verify($plainPassword, $hash1));
        $this->assertTrue(password_verify($plainPassword, $hash2));
    }

    /**
     * Test that multiple setPassword calls on same instance overwrite
     */
    public function testMultipleSetPasswordCallsOverwrite(): void
    {
        $firstPassword = 'password1';
        $secondPassword = 'password2';

        $this->receptionist->setPassword($firstPassword);
        $firstHash = $this->receptionist->getPassword();

        $this->receptionist->setPassword($secondPassword);
        $secondHash = $this->receptionist->getPassword();

        // Hashes should be different
        $this->assertNotEquals($firstHash, $secondHash);

        // Only the second password should verify
        $this->assertFalse(password_verify($firstPassword, $secondHash));
        $this->assertTrue(password_verify($secondPassword, $secondHash));
    }

    /**
     * Test getUserIdentifier returns email
     */
    public function testGetUserIdentifierReturnsEmail(): void
    {
        $email = 'receptionist@garage.com';
        $this->receptionist->setEmail($email);

        $this->assertSame($email, $this->receptionist->getUserIdentifier());
    }

    /**
     * Test getRoles returns ROLE_RECEPTIONIST
     */
    public function testGetRolesReturnsReceptionistRole(): void
    {
        $roles = $this->receptionist->getRoles();

        $this->assertIsArray($roles);
        $this->assertContains('ROLE_RECEPTIONIST', $roles);
        $this->assertCount(1, $roles);
    }

    /**
     * Test password is serialized with CRC32C hash (security feature)
     */
    public function testPasswordIsSerializedWithCRC32CHash(): void
    {
        $plainPassword = 'testPassword123';
        $this->receptionist->setPassword($plainPassword);

        $hashedPassword = $this->receptionist->getPassword();
        $serialized = $this->receptionist->__serialize();

        // The serialized password should be a CRC32C hash of the stored hash
        $expectedHash = hash('crc32c', $hashedPassword);

        // Access private property via array representation
        $key = "\0" . Receptionist::class . "\0password";
        $this->assertArrayHasKey($key, $serialized);
        $this->assertSame($expectedHash, $serialized[$key]);
    }

    /**
     * Test basic getter/setter for firstName
     */
    public function testSetAndGetFirstName(): void
    {
        $firstName = 'Marie';
        $this->receptionist->setFirstName($firstName);

        $this->assertSame($firstName, $this->receptionist->getFirstName());
    }

    /**
     * Test basic getter/setter for lastName
     */
    public function testSetAndGetLastName(): void
    {
        $lastName = 'Martin';
        $this->receptionist->setLastName($lastName);

        $this->assertSame($lastName, $this->receptionist->getLastName());
    }

    /**
     * Test basic getter/setter for email
     */
    public function testSetAndGetEmail(): void
    {
        $email = 'receptionist@example.com';
        $this->receptionist->setEmail($email);

        $this->assertSame($email, $this->receptionist->getEmail());
    }

    /**
     * Test that setPassword returns static for fluent interface
     */
    public function testSetPasswordReturnsStatic(): void
    {
        $result = $this->receptionist->setPassword('password');

        $this->assertInstanceOf(Receptionist::class, $result);
        $this->assertSame($this->receptionist, $result);
    }

    /**
     * Test that hash is different from plain password even for short passwords
     */
    public function testHashIsDifferentEvenForShortPasswords(): void
    {
        $shortPassword = 'abc';

        $this->receptionist->setPassword($shortPassword);
        $storedPassword = $this->receptionist->getPassword();

        $this->assertNotEquals($shortPassword, $storedPassword);
        $this->assertTrue(password_verify($shortPassword, $storedPassword));
    }

    /**
     * Test that hash is different from plain password even for long passwords
     */
    public function testHashIsDifferentEvenForLongPasswords(): void
    {
        $longPassword = str_repeat('a', 100);

        $this->receptionist->setPassword($longPassword);
        $storedPassword = $this->receptionist->getPassword();

        $this->assertNotEquals($longPassword, $storedPassword);
        $this->assertTrue(password_verify($longPassword, $storedPassword));
    }
}
