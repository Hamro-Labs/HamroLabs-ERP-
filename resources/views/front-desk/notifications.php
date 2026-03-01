<?php
/**
 * Front Desk — Notification History
 * Audit trail of communications
 */

if (!isset($_GET['partial'])) {
    renderFrontDeskHeader();
    renderFrontDeskSidebar();
}

$db = getDBConnection();
$tenantId = $_SESSION['userData']['tenant_id'];

// Get recent notifications/communications
$stmt = $db->prepare("
    SELECT * FROM notifications 
    WHERE tenant_id = :tid 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute(['tid' => $tenantId]);
$logs = $stmt->fetchAll();
?>

<div class="pg-head">
    <div class="pg-title">Communication Audit Log</div>
    <div class="pg-sub">Track all SMS and Email notifications sent by the system</div>
</div>

<div class="card">
    <div class="ct"><i class="fa-solid fa-clock-rotate-left"></i> Recent Dispatches</div>
    
    <div class="table-responsive">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Type</th>
                    <th>Message Snippet</th>
                    <th>Status</th>
                    <th>Sent At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): 
                    $typeClass = ($log['type'] == 'sms') ? 'ic-amber' : 'ic-blue';
                    $statusClass = ($log['status'] == 'sent') ? 'pg' : 'pr';
                ?>
                <tr>
                    <td>
                        <div class="nm"><?php echo htmlspecialchars($log['recipient_name'] ?? 'Multiple Recip.'); ?></div>
                        <div class="sub-txt"><?php echo htmlspecialchars($log['recipient_address'] ?? ''); ?></div>
                    </td>
                    <td>
                        <div class="sc-ico <?php echo $typeClass; ?>" style="width:30px; height:30px; font-size:12px;">
                            <i class="fa-solid <?php echo ($log['type'] == 'sms') ? 'fa-comment-sms' : 'fa-envelope'; ?>"></i>
                        </div>
                    </td>
                    <td style="max-width:300px;">
                        <div class="sub-txt" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($log['message']); ?>">
                            <?php echo htmlspecialchars($log['message']); ?>
                        </div>
                    </td>
                    <td><span class="pill <?php echo $statusClass; ?>"><?php echo ucfirst($log['status']); ?></span></td>
                    <td><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">No records found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
