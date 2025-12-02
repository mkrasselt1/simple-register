<?php
/**
 * Payment API Endpoint
 */

require_once __DIR__ . '/../controllers/PaymentController.php';

$controller = new PaymentController();
$controller->process();
