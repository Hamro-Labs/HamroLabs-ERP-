<?php
require 'config/config.php';
$db = getDBConnection();
$user = $db->query("SELECT email FROM users WHERE status = 'active' LIMIT 1")->fetch();
echo "Email: " . $user['email'] . "\n";
$db->query("UPDATE users SET password_hash = '" . password_hash("password123", PASSWORD_DEFAULT) . "' WHERE email = '" . $user['email'] . "'");
echo "Password set to: password123\n";
