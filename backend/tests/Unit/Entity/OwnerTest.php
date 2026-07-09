<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Owner;
use PHPUnit\Framework\TestCase;

class OwnerTest extends TestCase
{
    private Owner $owner;

    protected function setUp(): void
    {
        $this->owner = new Owner();
    }

    /**
     * Test that setPassword() stores the value as-is (expects pre-hashed password)
     */
    public function testSetPasswordStoresValueAsIs(): void
    {
        $preHashedPassword = '$2y$13$fakehash123456789012345678901234567890123456789012';

        $this->owner->setPassword($preHashedPassword);

        $this->assertSame($preHashedPassword, $this->owner->getPassword());
    }

    /**
     * Test that setPassword() does NOT hash internally
     * This is expected behavior - Owner expects already-hashed passwords
     */
    public function testSetPasswordDoesNotHashInternally(): void
    {
        $plainPassword = 'myPlainPassword123';

        $this->owner->setPassword($plainPassword);

        // The stored password should be exactly what was passed
        $this->assertSame($plainPassword, $this->owner->getPassword());

        // Verify it's NOT hashed by checking it doesn't start with $2y$ (bcrypt indicator)
        // Note: This test documents current behavior - password should be hashed externally
        $this->assertStringNotContainsString('$2y$', $plainPassword);
    }

    /**
     * Test getUserIdentifier returns email
     */
    public function testGetUserIdentifierReturnsEmail(): void
    {
        $email = 'owner@garage.com';
        $this->owner->setEmail($email);

        $this->assertSame($email, $this->owner->getUserIdentifier());
    }

    /**
     * Test getRoles returns ROLE_OWNER
     */
    public function testGetRolesReturnsOwnerRole(): void
    {
        $roles = $this->owner->getRoles();

        $this->assertIsArray($roles);
        $this->assertContains('ROLE_OWNER', $roles);
        $this->assertCount(1, $roles);
    }

    /**
     * Test password is serialized with CRC32C hash (security feature)
     */
    public function testPasswordIsSerializedWithCRC32CHash(): void
    {
        $plainPassword = 'testPassword123';
        $this->owner->setPassword($plainPassword);

        $serialized = $this->owner->__serialize();

        // The serialized password should be a CRC32C hash of the original
        $expectedHash = hash('crc32c', $plainPassword);

        // Access private property via array representation
        $key = "\0" . Owner::class . "\0password";
        $this->assertArrayHasKey($key, $serialized);
        $this->assertSame($expectedHash, $serialized[$key]);
    }

    /**
     * Test basic getter/setter for firstName
     */
    public function testSetAndGetFirstName(): void
    {
        $firstName = 'Jean';
        $this->owner->setFirstName($firstName);

        $this->assertSame($firstName, $this->owner->getFirstName());
    }

    /**
     * Test basic getter/setter for lastName
     */
    public function testSetAndGetLastName(): void
    {
        $lastName = 'Dupont';
        $this->owner->setLastName($lastName);

        $this->assertSame($lastName, $this->owner->getLastName());
    }

    /**
     * Test basic getter/setter for email
     */
    public function testSetAndGetEmail(): void
    {
        $email = 'owner@example.com';
        $this->owner->setEmail($email);

        $this->assertSame($email, $this->owner->getEmail());
    }

    /**
     * Test that multiple setPassword calls with same value produce same result
     * (unlike Receptionist which hashes)
     */
    public function testMultipleSetPasswordCallsProduceSameResult(): void
    {
        $password = 'samePassword';

        $this->owner->setPassword($password);
        $firstResult = $this->owner->getPassword();

        $this->owner->setPassword($password);
        $secondResult = $this->owner->getPassword();

        // Owner stores as-is, so both should be identical
        $this->assertSame($firstResult, $secondResult);
    }

    /**
     * Test that Owner expects already-hashed password from external hasher
     * This documents the expected usage pattern
     */
    public function testExpectedUsageWithExternalHasher(): void
    {
        // Simulate what PasswordHasher would produce
        $plainPassword = 'mySecretPassword';
        $externallyHashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Owner should store the hashed password as-is
        $this->owner->setPassword($externallyHashedPassword);

        $storedPassword = $this->owner->getPassword();

        // Verify it's the hashed version
        $this->assertSame($externallyHashedPassword, $storedPassword);

        // Verify we can verify it
        $this->assertTrue(password_verify($plainPassword, $storedPassword));
    }
}
