<?php
$host = 'localhost';
$dbname = 'hamrolabs_db';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create email_settings table (already used in ia-settings.js but missing in DB)
    $sqlSettings = "
    CREATE TABLE IF NOT EXISTS `email_settings` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `tenant_id` bigint(20) unsigned NOT NULL,
      `sender_name` varchar(255) DEFAULT NULL,
      `reply_to_email` varchar(255) DEFAULT NULL,
      `is_active` tinyint(1) NOT NULL DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_email_settings_tenant` (`tenant_id`),
      CONSTRAINT `fk_email_settings_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $db->exec($sqlSettings);
    echo "Table 'email_settings' created or verified.\n";

    // 2. Create email_templates table (for the 10 customizable templates)
    $sqlTemplates = "
    CREATE TABLE IF NOT EXISTS `email_templates` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `tenant_id` bigint(20) unsigned NOT NULL,
      `template_key` varchar(50) NOT NULL COMMENT 'e.g., welcome_email, payment_success, fee_reminder, etc.',
      `template_name` varchar(100) NOT NULL COMMENT 'Human readable name',
      `subject` varchar(255) NOT NULL,
      `body_content` text NOT NULL COMMENT 'HTML content with {{placeholders}}',
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_template_key_tenant` (`tenant_id`, `template_key`),
      CONSTRAINT `fk_email_templates_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $db->exec($sqlTemplates);
    echo "Table 'email_templates' created or verified.\n";

    // 3. Seed default templates for existing tenants
    $stmtTenants = $db->query("SELECT id FROM tenants");
    $tenants = $stmtTenants->fetchAll(PDO::FETCH_COLUMN);

    $defaultTemplates = [
        ['welcome_student', 'Welcome Student', 'Welcome to {{institute_name}}', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>Welcome to <strong>{{institute_name}}</strong>! We are thrilled to have you join our community.</p><p>We have received your enrollment and you can now log in to the student portal using the following credentials:</p><div style=\"background:#f0f9ff;padding:15px;border-radius:8px;margin:20px 0;\"><p style=\"margin:0;\"><strong>Email:</strong> {{email}}</p><p style=\"margin:5px 0 0;\"><strong>Password:</strong> {{plain_password}}</p></div><p>Please log in and update your password immediately.</p><br><p>Best regards,<br>{{institute_name}}</p></div>'],
        ['payment_success', 'Payment Successful', 'Payment Receipt - {{institute_name}}', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>We have successfully received your payment of <strong>{{amount}}</strong>.</p><p>Payment Mode: {{payment_mode}}</p><p>Receipt Number: {{receipt_no}}</p><br><p>Thank you for your prompt payment.</p><br><p>Best regards,<br>Accounts Team, {{institute_name}}</p></div>'],
        ['fee_reminder', 'Fee Reminder', 'Upcoming Fee Reminder - {{institute_name}}', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>This is a gentle reminder that your {{fee_type}} of <strong>{{amount}}</strong> is due on <strong>{{due_date}}</strong>.</p><p>Please ensure timely payment to avoid late fines.</p><br><p>Best regards,<br>Accounts Team, {{institute_name}}</p></div>'],
        ['exam_schedule', 'Exam Schedule Published', 'New Exam Schedule: {{exam_name}}', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>The schedule for <strong>{{exam_name}}</strong> has been published. The exam will start on <strong>{{start_date}}</strong>.</p><p>Please log in to the portal to view the full timetable.</p><br><p>Best of luck,<br>{{institute_name}}</p></div>'],
        ['exam_result', 'Exam Result Published', 'Results for {{exam_name}}', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>The results for <strong>{{exam_name}}</strong> have been published.</p><p>Please log in to your student portal to view your grades and feedback.</p><br><p>Best regards,<br>{{institute_name}}</p></div>'],
        ['course_enrollment', 'Course Enrollment', 'Enrolled in {{course_name}}', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>You have been successfully enrolled in <strong>{{course_name}}</strong>.</p><p>Your classes in batch <strong>{{batch_name}}</strong> start on {{start_date}}.</p><br><p>Best regards,<br>{{institute_name}}</p></div>'],
        ['attendance_warning', 'Attendance Warning', 'Low Attendance Alert', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>This is a notification regarding your attendance in <strong>{{course_name}}</strong>.</p><p>Your current attendance is <strong>{{attendance_percentage}}%</strong>, which is below the required threshold.</p><p>Please ensure you attend the remaining classes regularly.</p><br><p>Best regards,<br>{{institute_name}}</p></div>'],
        ['assignment_new', 'New Assignment', 'New Assignment: {{assignment_title}}', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>A new assignment <strong>\"{{assignment_title}}\"</strong> has been posted in {{course_name}}.</p><p><strong>Due Date:</strong> {{due_date}}</p><p>Log in to the portal to view the details and submit your work.</p><br><p>Best regards,<br>{{institute_name}}</p></div>'],
        ['general_announcement', 'General Announcement', 'Important Announcement from {{institute_name}}', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><h3>{{announcement_title}}</h3><p>{{announcement_content}}</p><br><p>Best regards,<br>{{institute_name}}</p></div>'],
        ['account_suspension', 'Account Suspended', 'Account Suspension Notice', '<div style=\"font-family:sans-serif;color:#333;\"><p>Dear {{student_name}},</p><p>Your student account at {{institute_name}} has been temporarily suspended.</p><p>Reason: {{suspension_reason}}</p><p>Please contact the administration office immediately to resolve this issue.</p><br><p>Best regards,<br>Admin Office, {{institute_name}}</p></div>']
    ];

    $insertStmt = $db->prepare("
        INSERT IGNORE INTO email_templates (tenant_id, template_key, template_name, subject, body_content) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $count = 0;
    foreach ($tenants as $tenantId) {
        foreach ($defaultTemplates as $tpl) {
            $insertStmt->execute([$tenantId, $tpl[0], $tpl[1], $tpl[2], $tpl[3]]);
            if ($insertStmt->rowCount() > 0) $count++;
        }
    }
    echo "Seeded $count default templates for all tenants.\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "\n";
}
