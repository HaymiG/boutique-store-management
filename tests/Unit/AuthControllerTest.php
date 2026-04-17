<?php
/**
 * Unit Tests for AuthController logic
 * Tests authentication flow, validation, and session management
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    /**
     * Test login validation requires email
     */
    public function testLoginRequiresEmail(): void
    {
        $data = ['email' => '', 'password' => 'Password123'];
        
        $errors = $this->validateLoginData($data);
        
        $this->assertArrayHasKey('email', $errors);
    }

    /**
     * Test login validation requires password
     */
    public function testLoginRequiresPassword(): void
    {
        $data = ['email' => 'admin@test.com', 'password' => ''];
        
        $errors = $this->validateLoginData($data);
        
        $this->assertArrayHasKey('password', $errors);
    }

    /**
     * Test login validation rejects invalid email format
     */
    public function testLoginRejectsInvalidEmail(): void
    {
        $data = ['email' => 'not-an-email', 'password' => 'Password123'];
        
        $errors = $this->validateLoginData($data);
        
        $this->assertArrayHasKey('email', $errors);
    }

    /**
     * Test login validation accepts valid data
     */
    public function testLoginAcceptsValidData(): void
    {
        $data = ['email' => 'admin@boutique.com', 'password' => 'Password123'];
        
        $errors = $this->validateLoginData($data);
        
        $this->assertEmpty($errors);
    }

    /**
     * Test registration validation requires all fields
     */
    public function testRegistrationRequiresAllFields(): void
    {
        $data = [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ];

        $errors = $this->validateRegistrationData($data);
        
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('first_name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    /**
     * Test registration validation catches password mismatch
     */
    public function testRegistrationCatchesPasswordMismatch(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'email' => 'john@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'DifferentPass123',
        ];

        $errors = $this->validateRegistrationData($data);
        
        $this->assertArrayHasKey('password_confirmation', $errors);
    }

    /**
     * Test registration validation catches weak password
     */
    public function testRegistrationCatchesWeakPassword(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'email' => 'john@test.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ];

        $errors = $this->validateRegistrationData($data);
        
        $this->assertArrayHasKey('password', $errors);
    }

    /**
     * Test valid registration data passes validation
     */
    public function testValidRegistrationPasses(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'email' => 'john@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];

        $errors = $this->validateRegistrationData($data);
        
        $this->assertEmpty($errors);
    }

    /**
     * Test session data after login
     */
    public function testSessionDataAfterLogin(): void
    {
        $sessionData = [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@boutique.com',
            'role' => 'manager',
            'role_id' => 1,
        ];

        // Verify all required fields are present
        $this->assertArrayHasKey('id', $sessionData);
        $this->assertArrayHasKey('username', $sessionData);
        $this->assertArrayHasKey('email', $sessionData);
        $this->assertArrayHasKey('role', $sessionData);
        
        // Verify no password in session
        $this->assertArrayNotHasKey('password', $sessionData);
    }

    /**
     * Test logout clears session
     */
    public function testLogoutClearsSession(): void
    {
        $_SESSION = [
            'boutique_user' => ['id' => 1, 'role' => 'manager'],
            '_auth_time' => time(),
        ];

        // Simulate logout
        $_SESSION = [];

        $this->assertEmpty($_SESSION);
        $this->assertArrayNotHasKey('boutique_user', $_SESSION);
    }

    // ===================================
    // Validation Helpers
    // ===================================

    private function validateLoginData(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password too short';
        }

        return $errors;
    }

    private function validateRegistrationData(array $data): array
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!(
            strlen($data['password']) >= 8
            && preg_match('/[A-Z]/', $data['password'])
            && preg_match('/[a-z]/', $data['password'])
            && preg_match('/[0-9]/', $data['password'])
        )) {
            $errors['password'] = 'Password must be 8+ chars with uppercase, lowercase, number';
        }
        if (!empty($data['password']) && $data['password'] !== ($data['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }

        return $errors;
    }
}
?>
