<?php
/**
 * Global Search API Test Suite
 * 
 * Tests the global search API endpoints for Institute Admin
 * Run: php tests/GlobalSearchTest.php
 * 
 * Requirements:
 * - PHP with cURL extension
 * - ERP app running and accessible at APP_URL
 */

require_once __DIR__ . '/../config/config.php';

class GlobalSearchTest {
    private $baseUrl;
    private $testResults = [];
    private $passed = 0;
    private $failed = 0;
    private $sessionToken = null;
    
    public function __construct() {
        $this->baseUrl = defined('APP_URL') ? APP_URL : 'http://localhost/erp';
    }
    
    /**
     * Run all API tests
     */
    public function runAllTests() {
        echo "\n========================================\n";
        echo "  Global Search API Tests\n";
        echo "========================================\n\n";
        
        // First, login to get session
        $this->login();
        
        if (!$this->sessionToken) {
            echo "ERROR: Could not login. Skipping search tests.\n";
            $this->printSummary();
            return;
        }
        
        // Run search tests
        $this->testSearchStudentsByName();
        $this->testSearchStudentsByRollNo();
        $this->testSearchStudentsByPhone();
        $this->testSearchStudentsByEmail();
        $this->testSearchTeachersByName();
        $this->testSearchTeachersByRole();
        $this->testSearchBatchesByName();
        $this->testSearchCoursesByName();
        $this->testSearchWithShortQuery();
        $this->testSearchWithNoResults();
        $this->testSearchWithoutAuthentication();
        
        $this->printSummary();
    }
    
    /**
     * Login to get session
     */
    private function login() {
        echo "--- Login (Setup) ---\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/login');
        curl_setopt($ch, CURLOPT_POST, true);
        // Login API expects form-encoded fields (not JSON)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'email' => 'admin@hamrolabs.edu.np',
            'password' => 'admin123',
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookies.txt');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if ($httpCode === 200 && is_array($data) && !empty($data['success'])) {
            $this->sessionToken = true;
            echo "✓ Login successful\n\n";
        } else {
            echo "✗ Login failed (HTTP $httpCode)\n";
            if (!empty($response)) {
                echo "  Response: $response\n\n";
            } else {
                echo "  No response body.\n\n";
            }
        }
    }
    
    /**
     * Test search students by name
     */
    private function testSearchStudentsByName() {
        echo "--- TC-GS-001: Search Students by Name ---\n";
        
        // Use a name that exists in realdb.sql (students.full_name)
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=Devbarat');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            isset($data['students']) && is_array($data['students']),
            'Response should have students array'
        );
        
        if (!empty($data['students'])) {
            $this->assertTrue(
                isset($data['students'][0]['name']),
                'Student should have name field'
            );
        }
        
        echo "\n";
    }
    
    /**
     * Test search students by roll number
     */
    private function testSearchStudentsByRollNo() {
        echo "--- TC-GS-002: Search Students by Roll Number ---\n";
        
        // Match roll_no like '00000...'
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=00000');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            isset($data['students']) && is_array($data['students']),
            'Response should have students array'
        );
        
        if (!empty($data['students'])) {
            $this->assertTrue(
                isset($data['students'][0]['roll_no']),
                'Student should have roll_no field'
            );
        }
        
        echo "\n";
    }
    
    /**
     * Test search students by phone
     */
    private function testSearchStudentsByPhone() {
        echo "--- TC-GS-003: Search Students by Phone ---\n";
        
        // Match phone fragment from realdb.sql
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=9800');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            isset($data['students']) && is_array($data['students']),
            'Response should have students array'
        );
        
        echo "\n";
    }
    
    /**
     * Test search students by email
     */
    private function testSearchStudentsByEmail() {
        echo "--- TC-GS-004: Search Students by Email ---\n";
        
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=gmail');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            isset($data['students']) && is_array($data['students']),
            'Response should have students array'
        );
        
        echo "\n";
    }
    
    /**
     * Test search teachers by name
     */
    private function testSearchTeachersByName() {
        echo "--- TC-GS-005: Search Teachers by Name ---\n";
        
        // Use teacher name fragment that exists in teachers.full_name
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=Ganesh');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            isset($data['teachers']) && is_array($data['teachers']),
            'Response should have teachers array'
        );
        
        if (!empty($data['teachers'])) {
            $this->assertTrue(
                isset($data['teachers'][0]['name']),
                'Teacher should have name field'
            );
            // Only name is guaranteed from API; phone/email are optional
        }
        
        echo "\n";
    }
    
    /**
     * Test search teachers by role
     */
    private function testSearchTeachersByRole() {
        echo "--- TC-GS-006: Search Teachers by Role ---\n";
        
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=principal');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            isset($data['teachers']) && is_array($data['teachers']),
            'Response should have teachers array'
        );
        
        echo "\n";
    }
    
    /**
     * Test search batches by name
     */
    private function testSearchBatchesByName() {
        echo "--- TC-GS-007: Search Batches by Name ---\n";
        
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=batch');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            isset($data['batches']) && is_array($data['batches']),
            'Response should have batches array'
        );
        
        if (!empty($data['batches'])) {
            $this->assertTrue(
                isset($data['batches'][0]['name']),
                'Batch should have name field'
            );
            $this->assertTrue(
                isset($data['batches'][0]['course_name']),
                'Batch should have course_name field'
            );
        }
        
        echo "\n";
    }
    
    /**
     * Test search courses by name
     */
    private function testSearchCoursesByName() {
        echo "--- TC-GS-008: Search Courses by Name ---\n";
        
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=course');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            isset($data['courses']) && is_array($data['courses']),
            'Response should have courses array'
        );
        
        if (!empty($data['courses'])) {
            $this->assertTrue(
                isset($data['courses'][0]['name']),
                'Course should have name field'
            );
        }
        
        echo "\n";
    }
    
    /**
     * Test search with short query (less than 2 chars)
     */
    private function testSearchWithShortQuery() {
        echo "--- TC-GS-009: Search with Short Query ---\n";
        
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=a');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        // Should return empty arrays for short queries
        $this->assertTrue(
            empty($data['students']) && empty($data['teachers']),
            'Should return empty arrays for short query'
        );
        
        echo "\n";
    }
    
    /**
     * Test search with no results
     */
    private function testSearchWithNoResults() {
        echo "--- TC-GS-010: Search with No Results ---\n";
        
        // Use a very specific random string that shouldn't match anything
        $response = $this->makeAuthenticatedRequest('/api/admin/global-search?q=xyznonexistent12345');
        $data = json_decode($response, true);
        
        $this->assertTrue(
            isset($data['success']) && $data['success'] === true,
            'Response should have success flag'
        );
        
        $this->assertTrue(
            empty($data['students']) && empty($data['teachers']) && 
            empty($data['batches']) && empty($data['courses']),
            'Should return empty arrays when no results'
        );
        
        $total = isset($data['total']) ? (int)$data['total'] : null;
        $this->assertTrue(
            $total === 0,
            'Total should be 0'
        );
        
        echo "\n";
    }
    
    /**
     * Test search without authentication
     */
    private function testSearchWithoutAuthentication() {
        echo "--- TC-GS-011: Search Without Authentication ---\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/admin/global-search?q=test');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertTrue(
            $httpCode === 401,
            'Should return 401 Unauthorized'
        );
        
        echo "\n";
    }
    
    /**
     * Make authenticated API request
     */
    private function makeAuthenticatedRequest($endpoint) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookies.txt');
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    /**
     * Make HTTP request
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    /**
     * Assert true
     */
    private function assertTrue($condition, $message) {
        if ($condition) {
            echo "  ✓ $message\n";
            $this->passed++;
        } else {
            echo "  ✗ $message\n";
            $this->failed++;
        }
    }
    
    /**
     * Print test summary
     */
    private function printSummary() {
        echo "========================================\n";
        echo "  Test Summary\n";
        echo "========================================\n";
        echo "  Passed: $this->passed\n";
        echo "  Failed: $this->failed\n";
        echo "  Total:  " . ($this->passed + $this->failed) . "\n";
        echo "========================================\n\n";
        
        // Clean up cookies
        if (file_exists(__DIR__ . '/cookies.txt')) {
            unlink(__DIR__ . '/cookies.txt');
        }
    }
}

// Run tests
$test = new GlobalSearchTest();
$test->runAllTests();
