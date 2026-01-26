<?php
/**
 * Articles management functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes.php';
require_once __DIR__ . '/views/_color_utils.php';

function getArticles() {
    if (!file_exists(ARTICLES_FILE)) {
        return [];
    }
    $content = file_get_contents(ARTICLES_FILE);
    $data = json_decode($content, true) ?: [];
    $articles = [];
    foreach ($data as $item) {
        $item['textColor'] = getContrastTextColor($item['color'] ?? '#007bff');
        $articles[$item['id']] = new Article($item);
    }
    return $articles;
}

function saveArticles($articles) {
    $data = [];
    foreach ($articles as $article) {
        $data[$article->id] = $article->toArray();
    }
    return file_put_contents(ARTICLES_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function addArticle($name, $price, $color = '#007bff') {
    $articles = getArticles();
    $id = bin2hex(random_bytes(8));
    $article = new Article([
        'id' => $id,
        'name' => $name,
        'price' => (float) $price,
        'color' => $color,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    $articles[$id] = $article;
    saveArticles($articles);
    return $id;
}

function updateArticle($id, $name, $price, $color = '#007bff') {
    $articles = getArticles();
    if (isset($articles[$id])) {
        $articles[$id]->name = $name;
        $articles[$id]->price = (float) $price;
        $articles[$id]->color = $color;
        saveArticles($articles);
        return true;
    }
    return false;
}

function deleteArticle($id) {
    $articles = getArticles();
    if (isset($articles[$id])) {
        unset($articles[$id]);
        saveArticles($articles);
        return true;
    }
    return false;
}

function getArticle($id) {
    $articles = getArticles();
    return $articles[$id] ?? null;
}
