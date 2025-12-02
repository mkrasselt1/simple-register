<?php
/**
 * Register View - Cash Register Interface
 */

require_once __DIR__ . '/controllers/RegisterController.php';

$controller = new RegisterController();
$controller->index();
