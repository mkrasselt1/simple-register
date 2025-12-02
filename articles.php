<?php
/**
 * Articles management functions
 */

require_once __DIR__ . '/config.php';

function getArticles() {
    if (!file_exists(ARTICLES_FILE)) {
        return [];
    }
    $content = file_get_contents(ARTICLES_FILE);
    return json_decode($content, true) ?: [];
}

function saveArticles($articles) {
    return file_put_contents(ARTICLES_FILE, json_encode($articles, JSON_PRETTY_PRINT));
}

function addArticle($name, $price, $color = '#007bff') {
    $articles = getArticles();
    $id = uniqid();
    $articles[$id] = [
        'id' => $id,
        'name' => $name,
        'price' => (float) $price,
        'color' => $color,
        'created_at' => date('Y-m-d H:i:s')
    ];
    saveArticles($articles);
    return $id;
}

function updateArticle($id, $name, $price, $color = '#007bff') {
    $articles = getArticles();
    if (isset($articles[$id])) {
        $articles[$id]['name'] = $name;
        $articles[$id]['price'] = (float) $price;
        $articles[$id]['color'] = $color;
        $articles[$id]['updated_at'] = date('Y-m-d H:i:s');
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
