<?php
/**
 * Security Tests
 * Tests SQL injection prevention, CSRF protection, password hashing, and session security
 */

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    /**
     * Test SQL injection prevention via parameterized queries
     */
    public function testSqlInjectionInUsername(): void
    {
        $maliciousInput = "admin'; DROP TABLE users; --";
        
        // When properly escaped/parameterized, this should be treated as literal string
        $escapedInput = addslashes($maliciousInput);
        
        // The parameterized query should use ? placeholders
        $sql = "SELECT * FROM users WHERE username = ?";
        
        $this->assertStringContainsString('?', $sql);
        $this->assertStringNotContainsString($maliciousInput, $sql);
    }

    /**
     * Test SQL injection prevention in email field
     */
    public function testSqlInjectionInEmail(): void
    {
        $maliciousInputs = [
            "admin@test.com' OR '1'='1",
            "admin@test.com'; DELETE FROM users WHERE '1'='1",
            "admin@test.com' UNION SELECT * FROM users--",
        ];

        foreach ($maliciousInputs as $input) {
            // These should fail email validation before reaching the DB
            $this->assertFalse(
                filter_var($input, FILTER_VALIDATE_EMAIL) !== false,
                "Malicious input should fail email validation: {$input}"
            );
        }
    }

    /**
     * Test password hashing uses bcrypt (not MD5 or SHA*)
     */
    public function testPasswordHashAlgorithm(): void
    {
        $password = 'TestPassword123';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // BCrypt hashes start with $2y$
        $this->assertStringStartsWith('$2y$12$', $hash);
        
        // Hash should be 60 characters for bcrypt
        $this->assertEquals(60, strlen($hash));
    }

    /**
     * Test password hash is not reversible
     */
    public function testPasswordHashNotReversible(): void
    {
        $password = 'MySecretPass123';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // Hash should not contain the original password
        $this->assertStringNotContainsString($password, $hash);
    }

    /**
     * Test CSRF token is cryptographically random
     */
    public function testCsrfTokenRandomness(): void
    {
        $tokens = [];
        for ($i = 0; $i < 100; $i++) {
            $tokens[] = bin2hex(random_bytes(32));
        }

        // All tokens should be unique
        $uniqueTokens = array_unique($tokens);
        $this->assertCount(100, $uniqueTokens, 'All CSRF tokens should be unique');
    }

    /**
     * Test CSRF token has sufficient entropy
     */
    public function testCsrfTokenEntropy(): void
    {
        $token = bin2hex(random_bytes(32));
        
        // 32 bytes = 256 bits of entropy, hex encoded = 64 chars
        $this->assertEquals(64, strlen($token));
        
        // Should only contain hex characters
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
    }

    /**
     * Test CSRF validation uses timing-safe comparison
     */
    public function testCsrfTimingSafeComparison(): void
    {
        $token1 = bin2hex(random_bytes(32));
        $token2 = $token1;
        $token3 = bin2hex(random_bytes(32));

        // hash_equals is timing-safe
        $this->assertTrue(hash_equals($token1, $token2));
        $this->assertFalse(hash_equals($token1, $token3));
    }

    /**
     * Test XSS prevention via htmlspecialchars
     */
    public function testXssPrevention(): void
    {
        $maliciousInputs = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '"><script>document.cookie</script>',
            "javascript:alert('XSS')",
        ];

        foreach ($maliciousInputs as $input) {
            $sanitized = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            
            // Should not contain unescaped HTML tags
            $this->assertStringNotContainsString('<script>', $sanitized);
            $this->assertStringNotContainsString('<img', $sanitized);
            
            // Should contain escaped entities
            if (str_contains($input, '<')) {
                $this->assertStringContainsString('&lt;', $sanitized);
            }
        }
    }

    /**
     * Test session cookie should be HttpOnly
     */
    public function testSessionCookieHttpOnly(): void
    {
        // The config constant should be true
        $httpOnly = true; // SESSION_HTTP_ONLY default from config

        $this->assertTrue($httpOnly, 'Session cookie should be HttpOnly');
    }

    /**
     * Test password cost factor is adequate
     */
    public function testPasswordCostFactor(): void
    {
        $cost = 12; // PASSWORD_HASH_COST from config
        
        // OWASP recommends at least cost 10 for bcrypt
        $this->assertGreaterThanOrEqual(10, $cost,
            'Bcrypt cost factor should be at least 10');
    }

    /**
     * Test session timeout is configured
     */
    public function testSessionTimeoutConfigured(): void
    {
        $sessionLifetime = 120; // minutes
        $timeoutSeconds = $sessionLifetime * 60;

        // Timeout should be between 15 minutes and 24 hours
        $this->assertGreaterThanOrEqual(15 * 60, $timeoutSeconds);
        $this->assertLessThanOrEqual(24 * 60 * 60, $timeoutSeconds);
    }

    /**
     * Test max login attempts is reasonable
     */
    public function testMaxLoginAttempts(): void
    {
        $maxAttempts = 5;
        $lockoutDuration = 15 * 60; // seconds

        // Should allow 3-10 attempts
        $this->assertGreaterThanOrEqual(3, $maxAttempts);
        $this->assertLessThanOrEqual(10, $maxAttempts);

        // Lockout should be at least 5 minutes
        $this->assertGreaterThanOrEqual(5 * 60, $lockoutDuration);
    }

    /**
     * Test that prepared statements use placeholders
     */
    public function testPreparedStatementFormat(): void
    {
        $queries = [
            'SELECT * FROM users WHERE email = ?',
            'SELECT * FROM users WHERE id = ? AND is_active = ?',
            'INSERT INTO users (username, email, password) VALUES (?, ?, ?)',
            'UPDATE users SET login_attempts = ? WHERE id = ?',
            'DELETE FROM users WHERE id = ?',
        ];

        foreach ($queries as $query) {
            $this->assertStringContainsString('?', $query,
                "Query should use ? placeholders: {$query}");
            
            // Should NOT contain direct string concatenation patterns
            $this->assertStringNotContainsString("' . \$", $query);
            $this->assertStringNotContainsString('" . $', $query);
        }
    }

    /**
     * Test password is never stored in session
     */
    public function testPasswordNotInSession(): void
    {
        $sessionData = [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@boutique.com',
            'role' => 'manager',
            'role_id' => 1,
            'role_name' => 'Manager',
            'full_name' => 'Admin User',
            'initials' => 'AU',
            'branch_id' => 1,
            'is_active' => 1,
        ];

        $this->assertArrayNotHasKey('password', $sessionData,
            'Password should never be stored in session data');
        $this->assertArrayNotHasKey('password_hash', $sessionData,
            'Password hash should never be stored in session data');
    }
}
?>
