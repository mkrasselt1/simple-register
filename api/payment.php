<?php
/**
 * Payment API Endpoint
 */

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../transactions.php';

// Set JSON content type
header('Content-Type: application/json');

// Authenticate
authenticate();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
if (!isset($input['items']) || !is_array($input['items']) || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No items in cart']);
    exit;
}

if (!isset($input['method']) || !in_array($input['method'], ['cash', 'card'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment method']);
    exit;
}

$total = isset($input['total']) ? (float) $input['total'] : 0;
$method = $input['method'];
$items = $input['items'];

// Log the transaction
try {
    $transactionId = logTransaction($items, $total, $method);
    echo json_encode([
        'success' => true,
        'transaction_id' => $transactionId,
        'message' => 'Payment processed successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to log transaction']);
}
