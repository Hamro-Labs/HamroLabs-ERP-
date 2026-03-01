<?php
/**
 * Login API Test Suite
 * 
 * Tests the authentication API endpoints
 * Run: php tests/LoginApiTest.php
 * 
 * Requires: cURL extension
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../bootstrap/app.php';

class LoginApiTest {
    private $baseUrl;
    private $testResults = [];
    private $passed = 0;
    private $failed = 0;
    
    public function __construct() {
        $this->baseUrl = defined('APP_URL') ? APP_URL : 'http://localhost/erp';
    }
    
    /**
     * Run all API tests
     */
    public function runAllTests() {
        echo "\n========================================\n";
        echo "  Login API Tests\n";
        echo "========================================\n\n";
        
        $this->testLoginEndpoint();
        $this->testLogoutEndpoint();
        $this->testRefreshEndpoint();
        $this->testPasswordResetEndpoint();
        
        $this->printSummary();
    }
    
    /**
     * Test login API endpoint
     */
    private function testLoginEndpoint() {
        echo "--- Login Endpoint Tests ---\n";
        
        // TC-001: Successful login
        $response = $this->makeRequest('/api/login', 'POST', [
            'email' => 'teacher@school.com',
            'password' => 'TestPass123!'
        ]);
        
        $this->assertTrue(
            isset($response['success']),
            'TC-001: Login endpoint returns response'
        );
        
        // TC-008: Invalid credentials
        $response = $this->makeRequest('/api/login', 'POST', [
            'email' => 'teacher@school.com',
            'password' => 'WrongPassword'
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-008: Invalid credentials rejected'
        );
        
        // TC-006: Invalid email format
        $response = $this->makeRequest('/api/login', 'POST', [
            'email' => 'invalid-email',
            'password' => 'TestPass123!'
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-006: Invalid email format rejected'
        );
        
        // TC-007: Non-existent user
        $response = $this->makeRequest('/api/login', 'POST', [
            'email' => 'nonexistent@test.com',
            'password' => 'TestPass123!'
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-007: Non-existent user rejected'
        );
        
        // TC-030: Inactive user
        $response = $this->makeRequest('/api/login', 'POST', [
            'email' => 'inactive@hamrolabs.com',
            'password' => 'TestPass123!'
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-030: Inactive account rejected'
        );
        
        // TC-009: Empty email
        $response = $this->makeRequest('/api/login', 'POST', [
            'email' => '',
            'password' => 'TestPass123!'
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-009: Empty email rejected'
        );
        
        // TC-010: Empty password
        $response = $this->makeRequest('/api/login', 'POST', [
            'email' => 'teacher@school.com',
            'password' => ''
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-010: Empty password rejected'
        );
    }
    
    /**
     * Test logout endpoint
     */
    private function testLogoutEndpoint() {
        echo "\n--- Logout Endpoint Tests ---\n";
        
        // First, login to get a token
        $loginResponse = $this->makeRequest('/api/login', 'POST', [
            'email' => 'teacher@school.com',
            'password' => 'TestPass123!'
        ]);
        
        // TC-025: Valid logout
        if (isset($loginResponse['refresh_token'])) {
            $response = $this->makeRequest('/api/logout', 'POST', [
                'refresh_token' => $loginResponse['refresh_token']
            ]);
            
            $this->assertTrue(
                $response['success'] ?? false,
                'TC-025: Logout with valid token succeeds'
            );
        }
        
        // TC-025: Logout without token
        $response = $this->makeRequest('/api/logout', 'POST', []);
        
        $this->assertTrue(
            $response['success'] ?? false,
            'TC-025: Logout without token succeeds (no-op)'
        );
    }
    
    /**
     * Test token refresh endpoint
     */
    private function testRefreshEndpoint() {
        echo "\n--- Token Refresh Tests ---\n";
        
        // First, login
        $loginResponse = $this->makeRequest('/api/login', 'POST', [
            'email' => 'teacher@school.com',
            'password' => 'TestPass123!'
        ]);
        
        // TC-024: Token refresh
        if (isset($loginResponse['refresh_token'])) {
            $response = $this->makeRequest('/api/refresh', 'POST', [
                'refresh_token' => $loginResponse['refresh_token']
            ]);
            
            $this->assertTrue(
                isset($response['access_token']),
                'TC-024: Refresh token returns new access token'
            );
        }
        
        // Invalid refresh token
        $response = $this->makeRequest('/api/refresh', 'POST', [
            'refresh_token' => 'invalid-token-12345'
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-066: Invalid refresh token rejected'
        );
        
        // TC-066: Token rotation (use same token twice)
        if (isset($loginResponse['refresh_token'])) {
            $firstResponse = $this->makeRequest('/api/refresh', 'POST', [
                'refresh_token' => $loginResponse['refresh_token']
            ]);
            
            if (isset($firstResponse['refresh_token'])) {
                $secondResponse = $this->makeRequest('/api/refresh', 'POST', [
                    'refresh_token' => $loginResponse['refresh_token']
                ]);
                
                $this->assertFalse(
                    $secondResponse['success'] ?? false,
                    'TC-066: Used refresh token is invalidated'
                );
            }
        }
        
        // Empty refresh token
        $response = $this->makeRequest('/api/refresh', 'POST', [
            'refresh_token' => ''
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-024: Empty refresh token rejected'
        );
    }
    
    /**
     * Test password reset endpoints
     */
    private function testPasswordResetEndpoint() {
        echo "\n--- Password Reset Tests ---\n";
        
        // TC-042: Request reset with valid email
        $response = $this->makeRequest('/api/send-password-reset', 'POST', [
            'email' => 'teacher@school.com'
        ]);
        
        $this->assertTrue(
            isset($response['success']),
            'TC-042: Password reset request processed'
        );
        
        // TC-043: Request reset with invalid email
        $response = $this->makeRequest('/api/send-password-reset', 'POST', [
            'email' => 'nonexistent@test.com'
        ]);
        
        // Should return success (don't reveal if email exists)
        $this->assertTrue(
            $response['success'] ?? false,
            'TC-043: Invalid email handled securely'
        );
        
        // TC-044: Empty email
        $response = $this->makeRequest('/api/send-password-reset', 'POST', [
            'email' => ''
        ]);
        
        $this->assertFalse(
            $response['success'] ?? false,
            'TC-044: Empty email rejected'
        );
    }
    
    /**
     * Test account lockout (TC-054 to TC-060)
     */
    private function testAccountLockout() {
        echo "\n--- Account Lockout Tests ---\n";
        
        // TC-054: Multiple failed attempts should trigger lockout
        $locked = false;
        
        for ($i = 0; $i < 6; $i++) {
            $response = $this->makeRequest('/api/login', 'POST', [
                'email' => 'locktest@test.com',
                'password' => 'WrongPassword' . $i
            ]);
            
            if (isset($response['error']) && strpos($response['error'], 'Too many') !== false) {
                $locked = true;
                break;
            }
        }
        
        $this->assertTrue(
            $locked,
            'TC-054: Account locked after 5 failed attempts'
        );
    }
    
    /**
     * Make HTTP request
     */
    private function makeRequest($endpoint, $method = 'GET', $data = []) {
        $url = $this->baseUrl . $endpoint;
        
        if (!function_exists('curl_init')) {
            echo "  ⚠ cURL not available, skipping API tests\n";
            return ['success' => false, 'error' => 'cURL not available'];
        }
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        // Add CSRF token if in session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['csrf_token'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-CSRF-Token: ' . $_SESSION['csrf_token']
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            return ['success' => false, 'error' => 'Request failed'];
        }
        
        $decoded = json_decode($response, true);
        
        return $decoded ?? ['raw' => $response, 'http_code' => $httpCode];
    }
    
    // ============================================
    // ASSERTION METHODS
    // ============================================
    
    private function assertTrue($condition, $message) {
        if ($condition) {
            echo "  ✓ PASS: $message\n";
            $this->passed++;
            $this->testResults[] = ['test' => $message, 'status' => 'PASS'];
        } else {
            echo "  ✗ FAIL: $message\n";
            $this->failed++;
            $this->testResults[] = ['test' => $message, 'status' => 'FAIL'];
        }
    }
    
    private function assertFalse($condition, $message) {
        $this->assertTrue(!$condition, $message);
    }
    
    private function printSummary() {
        $total = $this->passed + $this->failed;
        
        echo "\n========================================\n";
        echo "  TEST SUMMARY\n";
        echo "========================================\n";
        echo "  Total Tests: $total\n";
        echo "  Passed:      {$this->passed}\n";
        echo "  Failed:      {$this->failed}\n";
        echo "  Success Rate: " . round(($this->passed / $total) * 100, 1) . "%\n";
        echo "========================================\n\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $test = new LoginApiTest();
    $test->runAllTests();
}
