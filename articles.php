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

/**
 * Save all articles.
 * With $trackChanges the change time of new/modified articles is updated and
 * removed articles are remembered as deleted (needed for synchronisation).
 */
function saveArticles($articles, $trackChanges = true) {
    $now = date('Y-m-d H:i:s');
    $old = [];
    if ($trackChanges && file_exists(ARTICLES_FILE)) {
        $old = json_decode(file_get_contents(ARTICLES_FILE), true) ?: [];
    }
    $data = [];
    foreach ($articles as $article) {
        if ($trackChanges) {
            $prev = $old[$article->id] ?? null;
            if ($prev === null
                || ($prev['name'] ?? null) !== $article->name
                || (float) ($prev['price'] ?? 0) !== (float) $article->price
                || ($prev['color'] ?? null) !== $article->color) {
                $article->updated_at = $now;
            }
        }
        $data[$article->id] = $article->toArray();
    }
    if ($trackChanges) {
        $removed = array_diff(array_keys($old), array_keys($data));
        if ($removed) {
            $deleted = getDeletedArticles();
            foreach ($removed as $id) {
                $deleted[$id] = $now;
            }
            saveDeletedArticles($deleted);
        }
    }
    return file_put_contents(ARTICLES_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function getDeletedArticles() {
    if (!file_exists(DELETED_ARTICLES_FILE)) {
        return [];
    }
    return json_decode(file_get_contents(DELETED_ARTICLES_FILE), true) ?: [];
}

function saveDeletedArticles($deleted) {
    return file_put_contents(DELETED_ARTICLES_FILE, json_encode($deleted, JSON_PRETTY_PRINT), LOCK_EX);
}

function addArticle($name, $price, $color = '#007bff') {
    $articles = getArticles();
    $id = bin2hex(random_bytes(8));
    $article = new Article([
        'id' => $id,
        'name' => $name,
        'price' => (float) $price,
        'color' => $color,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
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
