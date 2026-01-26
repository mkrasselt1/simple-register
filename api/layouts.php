<?php
// api/layouts.php
// API für das Speichern und Laden von Layouts

$layoutsDir = __DIR__ . '/../data/layouts';
if (!is_dir($layoutsDir)) {
    mkdir($layoutsDir, 0777, true);
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';

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
        if (!$name) {
            echo json_encode(['success' => false, 'error' => 'No name']);
            break;
        }
        $file = "$layoutsDir/$name.json";
        if (!file_exists($file)) {
            echo json_encode(['success' => false, 'error' => 'Not found']);
            break;
        }
        $data = file_get_contents($file);
        echo json_encode(['success' => true, 'data' => json_decode($data, true)]);
        break;
    case 'save':
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? '';
        $data = $input['data'] ?? null;
        if (!$name || !$data) {
            echo json_encode(['success' => false, 'error' => 'Missing name or data']);
            break;
        }
        $file = "$layoutsDir/$name.json";
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
