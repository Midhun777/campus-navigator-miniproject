<?php
session_start();
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
require_once 'includes/db.php';
require_once 'includes/functions.php';
if ($userId) {
    audit_log($conn, 'logout', 'user', $userId, null);
}
session_unset();
session_destroy();
header('Location: login.php');
exit();