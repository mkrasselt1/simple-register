<?php
/**
 * Language API - Set language via AJAX
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Language.php';

$lang = new Language();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language = $_POST['language'] ?? 'en';
    $lang->setLanguage($language);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Invalid method']);
}
?>