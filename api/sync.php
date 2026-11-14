<?php
/**
 * Sync API - called by another Simple Register instance.
 *
 * POST JSON: {"username": "...", "password": "...", "snapshot": {...}}
 * The credentials must belong to an admin user of THIS instance.
 * Merges the received snapshot and answers with this instance's snapshot.
 */

require_once __DIR__ . '/../sync.php';
require_once __DIR__ . '/../core/Language.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => __('method_not_allowed', 'Method not allowed')]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => __('invalid_json', 'Invalid JSON input')]);
    exit;
}

if (!syncCheckAdminCredentials($input['username'] ?? '', $input['password'] ?? '')) {
    // Slow down password guessing
    sleep(1);
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => __('sync_error_auth', 'Invalid credentials')]);
    exit;
}

$snapshot = $input['snapshot'] ?? null;
if (!is_array($snapshot) || (int) ($snapshot['version'] ?? 0) !== SYNC_PROTOCOL_VERSION) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => __('sync_error_version', 'Incompatible sync version')]);
    exit;
}

$merged = syncMergeSnapshot($snapshot);

echo json_encode([
    'success' => true,
    'merged' => $merged,
    'snapshot' => syncBuildSnapshot(),
]);
