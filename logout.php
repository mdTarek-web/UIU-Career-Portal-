<?php
/**
 * UIU Career Portal - Logout Handler
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$app_root = get_app_root();
logout_user($app_root);
header("Location: {$app_root}/index.php");
exit;
