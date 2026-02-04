<?php
/**
 * Keep-alive API - Refresh session to prevent expiration
 */

require_once __DIR__ . '/../core/Controller.php';

session_start();

// Just accessing $_SESSION refreshes the session
$_SESSION['last_activity'] = time();

echo json_encode(['status' => 'ok']);
?>