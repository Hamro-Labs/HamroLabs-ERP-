<?php
require_once 'config.php';
$db = getDBConnection();
$db->exec("UPDATE users SET email='pdewbrath@gmail.com' WHERE id=1");
echo "Email updated for user 1.\n";
