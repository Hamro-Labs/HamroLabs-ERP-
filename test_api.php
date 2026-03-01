<?php
$_SESSION = ['userData' => ['tenant_id' => 1, 'role' => 'instituteadmin']];
$_GET = ['action' => 'summary'];

require __DIR__ . '/app/Http/Controllers/Admin/FeeReports.php';
