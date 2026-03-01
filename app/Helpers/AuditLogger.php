<?php
/**
 * AuditLogger Helper
 * Tracks every CUD (Create, Update, Delete) operation
 */

namespace App\Helpers;

class AuditLogger {
    /**
     * Log an action
     */
    public static function log($action, $tableName, $recordId, $oldValues = null, $newValues = null) {
        try {
            $tenantId = $_SESSION['userData']['tenant_id'] ?? null;
            $userId = $_SESSION['userData']['id'] ?? 1;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'system';

            // Filter out sensitive fields from logging (passwords, etc)
            $filter = ['password', 'password_hash', 'token', 'secret'];
            
            if ($oldValues) {
                if (is_array($oldValues)) {
                    foreach ($filter as $f) unset($oldValues[$f]);
                }
                $oldValues = is_string($oldValues) ? $oldValues : json_encode($oldValues);
            }
            
            if ($newValues) {
                if (is_array($newValues)) {
                    foreach ($filter as $f) unset($newValues[$f]);
                }
                $newValues = is_string($newValues) ? $newValues : json_encode($newValues);
            }

            if (class_exists('\Illuminate\Support\Facades\DB') && \Illuminate\Support\Facades\DB::getFacadeRoot()) {
                $db = \Illuminate\Support\Facades\DB::connection()->getPdo();
            } elseif (function_exists('getDBConnection')) {
                $db = getDBConnection();
            }

            $query = "INSERT INTO audit_logs (tenant_id, user_id, ip_address, action, table_name, record_id, changes, description) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $tenantId,
                $userId,
                $ipAddress,
                strtoupper($action),
                $tableName,
                $recordId,
                json_encode(['old' => $oldValues, 'new' => $newValues]),
                "Audited {$action} on {$tableName}"
            ]);
            
            return true;
        } catch (\Exception $e) {
            // Log to system error log if audit logging fails
            error_log("Audit Logger Failed: " . $e->getMessage());
            return false;
        }
    }
}
