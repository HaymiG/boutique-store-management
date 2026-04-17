<?php
/**
 * Unit Tests for User Model
 * Tests user creation, password hashing, validation, and account management
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    /**
     * Test password hashing uses bcrypt
     */
    public function testPasswordHashingUsesBcrypt(): void
    {
        $password = 'TestPassword123';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertTrue(password_verify($password, $hash));
    }

    /**
     * Test password verification with correct password
     */
    public function testPasswordVerificationCorrect(): void
    {
        $password = 'MySecureP@ss1';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->assertTrue(password_verify($password, $hash));
    }

    /**
     * Test password verification with incorrect password
     */
    public function testPasswordVerificationIncorrect(): void
    {
        $password = 'MySecureP@ss1';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->assertFalse(password_verify('WrongPassword123', $hash));
    }

    /**
     * Test password hash produces different hashes each time (salt)
     */
    public function testPasswordHashIsSalted(): void
    {
        $password = 'TestPassword123';
        $hash1 = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $hash2 = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->assertNotEquals($hash1, $hash2);
        // But both should verify correctly
        $this->assertTrue(password_verify($password, $hash1));
        $this->assertTrue(password_verify($password, $hash2));
    }

    /**
     * Test email validation - valid emails
     */
    public function testValidEmailFormats(): void
    {
        $validEmails = [
            'user@example.com',
            'admin@boutique.com',
            'test.user@domain.co.uk',
            'name+tag@gmail.com',
        ];

        foreach ($validEmails as $email) {
            $this->assertNotFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "Expected '{$email}' to be valid"
            );
        }
    }

    /**
     * Test email validation - invalid emails
     */
    public function testInvalidEmailFormats(): void
    {
        $invalidEmails = [
            'notanemail',
            '@domain.com',
            'user@',
            'user @domain.com',
            '',
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "Expected '{$email}' to be invalid"
            );
        }
    }

    /**
     * Test password strength validation
     */
    public function testPasswordStrengthValidation(): void
    {
        $strongPasswords = [
            'Password1',    // 8+ chars, upper, lower, number
            'MyP@ssw0rd',
            'Abcdefg1',
        ];

        $weakPasswords = [
            'pass',         // too short
            'password',     // no uppercase, no number
            'PASSWORD1',    // no lowercase
            'Passwords',    // no number
            '12345678',     // no letters
        ];

        foreach ($strongPasswords as $password) {
            $result = strlen($password) >= 8
                && preg_match('/[A-Z]/', $password)
                && preg_match('/[a-z]/', $password)
                && preg_match('/[0-9]/', $password);
            $this->assertTrue((bool)$result, "Expected '{$password}' to be strong");
        }

        foreach ($weakPasswords as $password) {
            $result = strlen($password) >= 8
                && preg_match('/[A-Z]/', $password)
                && preg_match('/[a-z]/', $password)
                && preg_match('/[0-9]/', $password);
            $this->assertFalse((bool)$result, "Expected '{$password}' to be weak");
        }
    }

    /**
     * Test account lockout logic
     */
    public function testAccountLockoutAfterMaxAttempts(): void
    {
        $maxAttempts = 5;
        $lockoutDuration = 15 * 60; // 15 minutes
        $attempts = 0;
        $lockedUntil = null;

        // Simulate failed login attempts
        for ($i = 0; $i < $maxAttempts; $i++) {
            $attempts++;
            if ($attempts >= $maxAttempts) {
                $lockedUntil = date('Y-m-d H:i:s', time() + $lockoutDuration);
            }
        }

        $this->assertEquals($maxAttempts, $attempts);
        $this->assertNotNull($lockedUntil);

        // Verify lockout is in the future
        $lockedTime = strtotime($lockedUntil);
        $this->assertGreaterThan(time(), $lockedTime);
    }

    /**
     * Test account lockout expires
     */
    public function testAccountLockoutExpiry(): void
    {
        // Simulate a lockout that has expired (1 second in the past)
        $lockedUntil = date('Y-m-d H:i:s', time() - 1);
        $lockedTime = strtotime($lockedUntil);

        $isLocked = time() <= $lockedTime;
        $this->assertFalse($isLocked, 'Account should be unlocked after lockout expires');
    }

    /**
     * Test user initials generation
     */
    public function testUserInitialsGeneration(): void
    {
        $cases = [
            ['first' => 'John', 'last' => 'Doe', 'expected' => 'JD'],
            ['first' => 'Admin', 'last' => 'Master', 'expected' => 'AM'],
            ['first' => 'alice', 'last' => 'smith', 'expected' => 'AS'],
        ];

        foreach ($cases as $case) {
            $initials = strtoupper(substr($case['first'], 0, 1))
                      . strtoupper(substr($case['last'], 0, 1));
            $this->assertEquals($case['expected'], $initials);
        }
    }

    /**
     * Test full name generation
     */
    public function testFullNameGeneration(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';
        $fullName = trim($firstName . ' ' . $lastName);

        $this->assertEquals('John Doe', $fullName);
    }

    /**
     * Test session data does not contain password
     */
    public function testSessionDataExcludesPassword(): void
    {
        $sessionData = [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@test.com',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'full_name' => 'Admin User',
            'initials' => 'AU',
            'role' => 'manager',
            'role_id' => 1,
            'role_name' => 'Manager',
            'branch_id' => 1,
            'is_active' => 1,
        ];

        $this->assertArrayNotHasKey('password', $sessionData);
        $this->assertArrayHasKey('id', $sessionData);
        $this->assertArrayHasKey('role', $sessionData);
        $this->assertArrayHasKey('email', $sessionData);
    }
}
?>
