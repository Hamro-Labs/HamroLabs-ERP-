<?php
/**
 * Update existing student roll numbers to 6-digit sequential format.
 */
require_once __DIR__ . '/../config/config.php';

try {
    $db = getDBConnection();
    
    // Get all tenants to process them separately (or all at once)
    $tenantsStmt = $db->query("SELECT DISTINCT tenant_id FROM students");
    $tenants = $tenantsStmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Starting roll number migration...\n";

    foreach ($tenants as $tid) {
        echo "Processing Tenant ID: $tid\n";
        
        // Get students ordered by ID (to maintain creation order)
        $stmt = $db->prepare("SELECT id FROM students WHERE tenant_id = :tid ORDER BY id ASC");
        $stmt->execute(['tid' => $tid]);
        $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $counter = 1;
        foreach ($students as $sid) {
            $newRoll = str_pad($counter, 6, '0', STR_PAD_LEFT);
            $update = $db->prepare("UPDATE students SET roll_no = :roll WHERE id = :id");
            $update->execute(['roll' => $newRoll, 'id' => $sid]);
            $counter++;
        }
        
        echo "Updated " . ($counter - 1) . " students for tenant $tid.\n";
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
