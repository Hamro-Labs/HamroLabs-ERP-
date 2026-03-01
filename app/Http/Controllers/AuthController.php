<?php

namespace App\Http\Controllers;

/**
 * Authentication Controller
 * JWT-based authentication for all 6 roles
 * 
 * Based on SRS v1.0 specifications:
 * - 8-hour access token + 30-day refresh token
 * - Refresh token rotation on every use
 * - 2FA mandatory for Super Admin and Institute Admin
 */

class AuthController {
    private $db;
    private $jwtSecret;
    private $accessTokenExpiry = 28800; // 8 hours in seconds
    private $refreshTokenExpiry = 2592000; // 30 days in seconds
    
    public function __construct() {
        $this->db = getDBConnection();
        $this->jwtSecret = defined('JWT_SECRET') ? JWT_SECRET : 'hamrolabs-erp-secret-key-2026';
    }
    
    /**
     * User login - returns JWT tokens
     */
    public function login() {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $otp = $_POST['otp'] ?? null;
        $remember = $_POST['remember'] ?? false; // Remember me handled by frontend token storage duration
        
        // Validate input
        if (empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Email and password are required'];
        }
        
        // Find user by email
        $user = $this->findUserByEmail($email);
        
        if (!$user) {
            $this->logLoginAttempt($email, null, 'failed', 'User not found');
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Check if user is active
        if ($user['status'] !== 'active') {
            $this->logLoginAttempt($email, $user['id'], 'failed', 'Account inactive');
            return ['success' => false, 'error' => 'Your account is not active'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->logLoginAttempt($email, $user['id'], 'failed', 'Invalid password');
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Check if 2FA is required
        if ($user['two_fa_enabled'] && ($user['role'] === 'superadmin' || $user['role'] === 'instituteadmin')) {
            if (empty($otp)) {
                // Return partial success - requires OTP
                return ['success' => true, 'requires_otp' => true, 'user_id' => $user['id']];
            }
            
            // Verify OTP
            if (!$this->verifyOTP($user['id'], $otp)) {
                $this->logLoginAttempt($email, $user['id'], 'failed', 'Invalid OTP');
                return ['success' => false, 'error' => 'Invalid OTP'];
            }
        }
        
        // Check rate limiting
        if (!$this->checkRateLimit($_SERVER['REMOTE_ADDR'])) {
            $this->logLoginAttempt($email, $user['id'], 'failed', 'Rate limited');
            return ['success' => false, 'error' => 'Too many login attempts. Please try again later.'];
        }
        
        // Generate tokens
        $accessToken = $this->generateAccessToken($user);
        $refreshToken = $this->generateRefreshToken($user);
        
        // Store refresh token
        $this->storeRefreshToken($user['id'], $refreshToken);
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        // Log successful login
        $this->logLoginAttempt($email, $user['id'], 'success');
        
        // Get redirect URL based on role
        $redirectUrl = $this->getRedirectUrl($user['role']);
        
        // Generate loading screen URL with token
        $loadingScreenUrl = APP_URL . '/loading?token=' . urlencode($accessToken) . '&redirect=' . urlencode($redirectUrl);
        
        return [
            'success' => true,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $this->accessTokenExpiry,
            'user' => $this->sanitizeUser($user),
            'redirect' => $redirectUrl,
            'loading_screen' => $loadingScreenUrl
        ];
    }
    
    /**
     * Refresh access token
     */
    public function refresh() {
        $refreshToken = $_POST['refresh_token'] ?? '';
        
        if (empty($refreshToken)) {
            return ['success' => false, 'error' => 'Refresh token required'];
        }
        
        // Verify refresh token
        $tokenData = $this->verifyRefreshToken($refreshToken);
        
        if (!$tokenData) {
            return ['success' => false, 'error' => 'Invalid or expired refresh token'];
        }
        
        // Get user
        $user = $this->findUserById($tokenData['user_id']);
        
        if (!$user || $user['status'] !== 'active') {
            return ['success' => false, 'error' => 'User not found or inactive'];
        }
        
        // Rotate refresh token (invalidate old one)
        $this->invalidateRefreshToken($refreshToken);
        
        // Generate new tokens
        $newAccessToken = $this->generateAccessToken($user);
        $newRefreshToken = $this->generateRefreshToken($user);
        
        // Store new refresh token
        $this->storeRefreshToken($user['id'], $newRefreshToken);
        
        return [
            'success' => true,
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in' => $this->accessTokenExpiry
        ];
    }
    
    /**
     * Logout - invalidate tokens
     */
    public function logout() {
        $refreshToken = $_POST['refresh_token'] ?? '';
        
        if (!empty($refreshToken)) {
            $this->invalidateRefreshToken($refreshToken);
        }
        
        // Clear session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        return ['success' => true];
    }
    
    /**
     * Send OTP for 2FA
     */
    public function sendOTP($userId) {
        $user = $this->findUserById($userId);
        
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        if (empty($user['phone'])) {
            return ['success' => false, 'error' => 'No phone number on file'];
        }
        
        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP (in production, use proper OTP storage with expiration)
        $this->storeOTP($userId, $otp);
        
        // Send SMS (in production, use Sparrow SMS API)
        $message = "Your HamroLabs ERP verification code is: $otp";
        // $this->sendSMS($user['phone'], $message);
        
        // For development, return OTP in response
        if (APP_ENV === 'development') {
            return ['success' => true, 'otp' => $otp, 'message' => 'OTP sent (development mode)'];
        }
        
        return ['success' => true, 'message' => 'OTP sent to your phone'];
    }
    
    /**
     * Find user by email
     */
    private function findUserByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE email = :email LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by ID
     */
    private function findUserById($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch();
    }
    
    /**
     * Generate JWT access token
     */
    private function generateAccessToken($user) {
        $payload = [
            'iss' => APP_URL,
            'aud' => APP_URL,
            'iat' => time(),
            'exp' => time() + $this->accessTokenExpiry,
            'user_id' => $user['id'],
            'tenant_id' => $user['tenant_id'],
            'role' => $user['role'],
            'type' => 'access'
            // TC-067: password and password_hash are EXPLICITLY excluded
        ];
        
        return $this->jwtEncode($payload);
    }
    
    /**
     * Generate JWT refresh token
     */
    private function generateRefreshToken($user) {
        $payload = [
            'iss' => APP_URL,
            'aud' => APP_URL,
            'iat' => time(),
            'exp' => time() + $this->refreshTokenExpiry,
            'user_id' => $user['id'],
            'type' => 'refresh',
            'jti' => bin2hex(random_bytes(16)) // Unique token ID
        ];
        
        return $this->jwtEncode($payload);
    }
    
    /**
     * Encode JWT
     */
    private function jwtEncode($payload) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = base64_encode(hash_hmac('sha256', "$header.$payloadEncoded", $this->jwtSecret, true));
        
        return "$header.$payloadEncoded.$signature";
    }
    
    /**
     * Decode JWT
     */
    public function jwtDecode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        $signature = base64_encode(hash_hmac('sha256', "$parts[0].$parts[1]", $this->jwtSecret, true));
        
        if ($signature !== $parts[2]) {
            return null;
        }
        
        return json_decode(base64_decode($parts[1]), true);
    }
    
    /**
     * Store refresh token
     */
    private function storeRefreshToken($userId, $token) {
        $stmt = $this->db->prepare("
            INSERT INTO refresh_tokens (user_id, token, expires_at)
            VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 30 DAY))
        ");
        $stmt->execute([
            'user_id' => $userId,
            'token' => hash('sha256', $token)
        ]);
    }
    
    /**
     * Verify refresh token
     */
    private function verifyRefreshToken($token) {
        $tokenHash = hash('sha256', $token);
        
        $stmt = $this->db->prepare("
            SELECT * FROM refresh_tokens 
            WHERE token = :token AND expires_at > NOW() AND invalidated = 0
            LIMIT 1
        ");
        $stmt->execute(['token' => $tokenHash]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return null;
        }
        
        return $this->jwtDecode($token);
    }
    
    /**
     * Invalidate refresh token
     */
    private function invalidateRefreshToken($token) {
        $tokenHash = hash('sha256', $token);
        
        $stmt = $this->db->prepare("
            UPDATE refresh_tokens SET invalidated = 1 WHERE token = :token
        ");
        $stmt->execute(['token' => $tokenHash]);
    }
    
    /**
     * Store OTP
     */
    private function storeOTP($userId, $otp) {
        $stmt = $this->db->prepare("
            INSERT INTO otp_codes (user_id, code, expires_at)
            VALUES (:user_id, :code, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
        ");
        $stmt->execute([
            'user_id' => $userId,
            'code' => $otp
        ]);
    }
    
    /**
     * Verify OTP
     */
    private function verifyOTP($userId, $otp) {
        $stmt = $this->db->prepare("
            SELECT * FROM otp_codes 
            WHERE user_id = :user_id AND code = :code AND expires_at > NOW() AND used = 0
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId, 'code' => $otp]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Mark OTP as used
            $stmt = $this->db->prepare("UPDATE otp_codes SET used = 1 WHERE id = :id");
            $stmt->execute(['id' => $result['id']]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check rate limiting
     */
    private function checkRateLimit($ip) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempts FROM login_attempts
            WHERE ip_address = :ip AND status = 'failed'
            AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute(['ip' => $ip]);
        $result = $stmt->fetch();
        
        return ($result['attempts'] ?? 0) < MAX_LOGIN_ATTEMPTS;
    }
    
    /**
     * Log login attempt
     */
    private function logLoginAttempt($email, $userId, $status, $reason = null) {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (email, ip_address, status, failure_reason)
            VALUES (:email, :ip, :status, :reason)
        ");
        
        $stmt->execute([
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'status' => $status,
            'reason' => $reason
        ]);
    }
    
    /**
     * Update last login
     */
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }
    
    /**
     * Get redirect URL based on role
     */
    private function getRedirectUrl($role) {
        $roleSlugMap = [
            'superadmin' => 'super-admin',
            'instituteadmin' => 'admin',
            'frontdesk' => 'front-desk',
            'teacher' => 'teacher',
            'student' => 'student',
            'guardian' => 'guardian',
        ];
        
        $slug = $roleSlugMap[$role] ?? 'login';
        return APP_URL . '/dash/' . $slug;
    }
    
    /**
     * Sanitize user data for response
     */
    private function sanitizeUser($user) {
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'tenant_id' => $user['tenant_id']
        ];
    }
    
    /**
     * Validate JWT token (for API auth)
     */
    public static function validateToken() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (empty($authHeader)) {
            return null;
        }
        
        if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            $auth = new self();
            $payload = $auth->jwtDecode($token);
            
            if ($payload && $payload['exp'] > time()) {
                return $payload;
            }
        }
        
        return null;
    }
}

// Handle API requests
if (php_sapi_name() === 'api' || isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    $auth = new AuthController();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            echo json_encode($auth->login());
            break;
            
        case 'refresh':
            echo json_encode($auth->refresh());
            break;
            
        case 'logout':
            echo json_encode($auth->logout());
            break;
            
        case 'send_otp':
            $userId = $_POST['user_id'] ?? 0;
            echo json_encode($auth->sendOTP($userId));
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
    
    exit;
}
?>
