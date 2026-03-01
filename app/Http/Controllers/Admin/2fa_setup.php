<?php
/**
 * 2FA Setup Controller — Admin
 * Manages Two-Factor Authentication settings and status
 */

require_once __DIR__ . '/../../../../config/config.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$tenantId = $_SESSION['userData']['tenant_id'] ?? null;
$userId = $_SESSION['userData']['id'] ?? null;

if (!$tenantId) {
    echo json_encode(['success' => false, 'message' => 'Tenant ID missing']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

try {
    $db = getDBConnection();

    switch ($action) {
        case 'status':
            // Get 2FA status for the institute/current user
            $stmt = $db->prepare("SELECT two_factor_enabled FROM users WHERE id = :uid AND tenant_id = :tid");
            $stmt->execute(['uid' => $userId, 'tid' => $tenantId]);
            $user = $stmt->fetch();
            
            // Also check institute-wide 2FA requirement
            $instStmt = $db->prepare("SELECT settings FROM tenants WHERE id = :tid");
            $instStmt->execute(['tid' => $tenantId]);
            $inst = $instStmt->fetch();
            $settings = json_decode($inst['settings'] ?? '{}', true);
            $enforce2FA = $settings['enforce_2fa'] ?? false;

            echo json_encode([
                'success' => true, 
                'data' => [
                    'user_enabled' => (bool)($user['two_factor_enabled'] ?? false),
                    'institute_enforced' => (bool)$enforce2FA
                ]
            ]);
            break;

        case 'toggle_institute':
            // Admin only
            if ($_SESSION['userData']['role'] !== 'instituteadmin') {
                throw new Exception("Only Institute Admins can change this setting");
            }

            $enabled = filter_var($_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            
            // Get current settings
            $stmt = $db->prepare("SELECT settings FROM tenants WHERE id = :tid");
            $stmt->execute(['tid' => $tenantId]);
            $tenant = $stmt->fetch();
            $settings = json_decode($tenant['settings'] ?? '{}', true);
            
            $settings['enforce_2fa'] = $enabled;
            
            $updateStmt = $db->prepare("UPDATE tenants SET settings = :settings WHERE id = :tid");
            $updateStmt->execute([
                'tid' => $tenantId, 
                'settings' => json_encode($settings)
            ]);

            echo json_encode(['success' => true, 'message' => 'Institute 2FA enforcement updated']);
            break;

        case 'setup_qr':
            // This is a stub for QR code generation
            // In a real implementation, you'd use a library like PHPGangsta_GoogleAuthenticator
            echo json_encode([
                'success' => true,
                'message' => '2FA Secret generated',
                'data' => [
                    'secret' => 'ABCDEFG123456', // Mock secret
                    'qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/HamroERP:Admin?secret=ABCDEFG123456&issuer=HamroERP'
                ]
            ]);
            break;

        default:
            throw new Exception("Unknown action: $action");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
