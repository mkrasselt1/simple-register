<?php
/**
 * Admin Panel - Manage Articles
 */

require_once __DIR__ . '/controllers/AdminController.php';

$controller = new AdminController();
$controller->index();
