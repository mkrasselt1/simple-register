<?php
/**
 * Language API - Set language via AJAX
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Language.php';

$lang = Language::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language = $_POST['language'] ?? 'en';
    $lang->setLanguage($language);
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid method']);
}
?>