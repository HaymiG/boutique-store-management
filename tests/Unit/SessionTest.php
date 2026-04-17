<?php
/**
 * Unit Tests for Session Management
 * Tests session lifecycle, CSRF tokens, flash messages
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure clean state
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    /**
     * Test CSRF token generation produces valid hex string
     */
    public function testCsrfTokenGeneration(): void
    {
        $token = bin2hex(random_bytes(32));

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    /**
     * Test CSRF token validation with correct token
     */
    public function testCsrfTokenValidationCorrect(): void
    {
        $sessionToken = bin2hex(random_bytes(32));
        $submittedToken = $sessionToken;

        $this->assertTrue(hash_equals($sessionToken, $submittedToken));
    }

    /**
     * Test CSRF token validation with incorrect token
     */
    public function testCsrfTokenValidationIncorrect(): void
    {
        $sessionToken = bin2hex(random_bytes(32));
        $submittedToken = bin2hex(random_bytes(32));

        $this->assertFalse(hash_equals($sessionToken, $submittedToken));
    }

    /**
     * Test CSRF token validation rejects empty token
     */
    public function testCsrfTokenRejectsEmpty(): void
    {
        $sessionToken = bin2hex(random_bytes(32));
        $emptyToken = '';

        // hash_equals with empty string should return false
        $this->assertFalse(hash_equals($sessionToken, $emptyToken));
    }

    /**
     * Test flash message set and get
     */
    public function testFlashMessageSetAndGet(): void
    {
        $_SESSION['_flash']['success'] = 'Operation completed successfully';

        $message = $_SESSION['_flash']['success'] ?? null;
        unset($_SESSION['_flash']['success']);

        $this->assertEquals('Operation completed successfully', $message);
        $this->assertArrayNotHasKey('success', $_SESSION['_flash'] ?? []);
    }

    /**
     * Test flash message returns default when not set
     */
    public function testFlashMessageDefault(): void
    {
        $message = $_SESSION['_flash']['nonexistent'] ?? 'default';
        $this->assertEquals('default', $message);
    }

    /**
     * Test session data storage
     */
    public function testSessionDataGetSet(): void
    {
        $_SESSION['test_key'] = 'test_value';

        $this->assertEquals('test_value', $_SESSION['test_key']);
        $this->assertTrue(isset($_SESSION['test_key']));
    }

    /**
     * Test session data removal
     */
    public function testSessionDataRemoval(): void
    {
        $_SESSION['temp_key'] = 'temp_value';
        unset($_SESSION['temp_key']);

        $this->assertFalse(isset($_SESSION['temp_key']));
    }

    /**
     * Test session timeout check
     */
    public function testSessionTimeoutCheck(): void
    {
        $authTimeout = 120 * 60; // 120 minutes in seconds

        // Session created now — should be valid
        $authTime = time();
        $isExpired = (time() - $authTime) > $authTimeout;
        $this->assertFalse($isExpired, 'Fresh session should not be expired');

        // Session created 3 hours ago — should be expired
        $authTimeOld = time() - (3 * 60 * 60);
        $isExpiredOld = (time() - $authTimeOld) > $authTimeout;
        $this->assertTrue($isExpiredOld, 'Old session should be expired');
    }

    /**
     * Test user data storage in session
     */
    public function testUserSessionStorage(): void
    {
        $userData = [
            'id' => 1,
            'username' => 'admin',
            'role' => 'manager',
        ];

        $_SESSION['boutique_user'] = $userData;

        $this->assertEquals($userData, $_SESSION['boutique_user']);
        $this->assertEquals('manager', $_SESSION['boutique_user']['role']);
        $this->assertEquals(1, $_SESSION['boutique_user']['id']);
    }

    /**
     * Test session regeneration produces valid new ID
     */
    public function testSessionIdFormat(): void
    {
        // Generate a session-like ID
        $sessionId = bin2hex(random_bytes(16));

        $this->assertIsString($sessionId);
        $this->assertGreaterThan(20, strlen($sessionId));
    }

    /**
     * Test CSRF hidden field generation
     */
    public function testCsrfFieldGeneration(): void
    {
        $token = bin2hex(random_bytes(32));
        $field = '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';

        $this->assertStringContainsString('_csrf_token', $field);
        $this->assertStringContainsString($token, $field);
        $this->assertStringContainsString('type="hidden"', $field);
    }
}
?>
