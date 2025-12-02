<?php
/**
 * Simple Register - Main Entry Point
 */

require_once __DIR__ . '/controllers/HomeController.php';

$controller = new HomeController();
$controller->index();
