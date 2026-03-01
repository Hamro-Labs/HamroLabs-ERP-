<?php
/**
 * Institute Admin — Sidebar Badge Counters
 * 
 * Fetches live counts for sidebar badge indicators.
 * All queries use prepared statements for security.
 * Designed to be lightweight — called once per page load.
 */

function getIASidebarBadges($tenantId) {
    if (!$tenantId) return [];

    $badges = [];
    try {
        $db = getDBConnection();

        // Total active students
        $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE tenant_id = :tid AND status = 'active'");
        $stmt->execute(['tid' => $tenantId]);
        $badges['total_students'] = (int) $stmt->fetchColumn();

        // Pending admissions (students created in last 7 days without confirmed status)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE tenant_id = :tid AND status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt->execute(['tid' => $tenantId]);
            $badges['pending_admissions'] = (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            $badges['pending_admissions'] = 0;
        }

        // Outstanding fee count (students with unpaid dues)
        try {
            $stmt = $db->prepare("SELECT COUNT(DISTINCT student_id) FROM fee_records WHERE tenant_id = :tid AND (amount_due - amount_paid) > 0");
            $stmt->execute(['tid' => $tenantId]);
            $badges['outstanding_count'] = (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            $badges['outstanding_count'] = 0;
        }

        // New inquiries (last 7 days)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM inquiries WHERE tenant_id = :tid AND status = 'new' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt->execute(['tid' => $tenantId]);
            $badges['new_inquiries'] = (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            $badges['new_inquiries'] = 0;
        }

        // Total study materials
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM study_materials WHERE tenant_id = :tid AND deleted_at IS NULL AND status = 'active'");
            $stmt->execute(['tid' => $tenantId]);
            $badges['total_materials'] = (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            $badges['total_materials'] = 0;
        }

    } catch (Exception $e) {
        // If DB connection fails, return empty badges — sidebar still renders
        error_log("Sidebar badge error: " . $e->getMessage());
    }

    return $badges;
}
