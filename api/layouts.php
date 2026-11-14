<?php
// api/layouts.php
// API für das Speichern und Laden von Layouts

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Language.php';

$layoutsDir = __DIR__ . '/../data/layouts';
if (!is_dir($layoutsDir)) {
    mkdir($layoutsDir, 0777, true);
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';

// Layout names are used as file names: reject anything that looks like a path
$isSafeName = function ($n) {
    return is_string($n) && $n !== '' && strlen($n) <= 100
        && strpos($n, '/') === false && strpos($n, '\\') === false
        && strpos($n, '..') === false && strpos($n, "\0") === false;
};

switch ($action) {
    case 'list':
        // Alle Layout-Namen auflisten
        $files = glob($layoutsDir . '/*.json');
        $names = array_map(function($f) {
            return basename($f, '.json');
        }, $files);
        echo json_encode(['success' => true, 'layouts' => $names]);
        break;
    case 'load':
        if (!$name || !$isSafeName($name)) {
            echo json_encode(['success' => false, 'error' => __('layout_no_name', 'No name')]);
            break;
        }
        $file = "$layoutsDir/$name.json";
        if (!file_exists($file)) {
            echo json_encode(['success' => false, 'error' => __('layout_not_found', 'Not found')]);
            break;
        }
        $data = file_get_contents($file);
        echo json_encode(['success' => true, 'data' => json_decode($data, true)]);
        break;
    case 'save':
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? '';
        $data = $input['data'] ?? null;
        if (!$name || !$data || !$isSafeName($name)) {
            echo json_encode(['success' => false, 'error' => __('layout_missing_data', 'Missing name or data')]);
            break;
        }
        $file = "$layoutsDir/$name.json";
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
        break;
    default:
        echo json_encode(['success' => false, 'error' => __('invalid_action', 'Invalid action')]);
}
