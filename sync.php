<?php
/**
 * Synchronisation with another Simple Register instance.
 *
 * Both instances exchange a snapshot of their data in a single request:
 * the local server sends its snapshot to <remote>/api/sync.php, the remote
 * merges it and answers with its own (merged) snapshot, which is then merged
 * locally. Afterwards both sides hold the same data.
 *
 * Merge rules:
 * - Articles:     newest change wins (updated_at); deletions are kept as
 *                 tombstones so a deleted article does not come back.
 * - Layouts:      newest file wins.
 * - Users:        users missing on one side are added; existing users are kept.
 * - Transactions: missing transactions are added; a cancellation on either
 *                 side is applied on both.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/articles.php';

define('SYNC_PROTOCOL_VERSION', 1);

// ---------------------------------------------------------------- snapshot

function syncBuildSnapshot() {
    $articles = file_exists(ARTICLES_FILE) ? (json_decode(file_get_contents(ARTICLES_FILE), true) ?: []) : [];

    $layouts = [];
    foreach (glob(LAYOUTS_DIR . '*.json') ?: [] as $file) {
        $name = basename($file, '.json');
        if (!syncIsSafeName($name)) {
            continue;
        }
        $layouts[$name] = [
            'updated_at' => filemtime($file),
            'data' => json_decode(file_get_contents($file), true),
        ];
    }

    $users = file_exists(USERS_FILE) ? (json_decode(file_get_contents(USERS_FILE), true) ?: []) : [];

    return [
        'version' => SYNC_PROTOCOL_VERSION,
        'articles' => $articles,
        'deleted_articles' => getDeletedArticles(),
        'layouts' => $layouts,
        'users' => $users,
        'transactions' => array_values(array_map(function ($t) {
            return $t['data'];
        }, syncLoadTransactions())),
    ];
}

/**
 * All local transactions, indexed by ID: [id => ['file' => path, 'data' => array]]
 */
function syncLoadTransactions() {
    $result = [];
    foreach (glob(TRANSACTIONS_DIR . '*.json') ?: [] as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data) && isset($data['id'])) {
            $result[$data['id']] = ['file' => $file, 'data' => $data];
        }
    }
    return $result;
}

// ------------------------------------------------------------------- merge

/**
 * Merge a snapshot from the other instance into the local data.
 * Returns counters of what changed locally.
 */
function syncMergeSnapshot($remote) {
    $stats = ['articles' => 0, 'layouts' => 0, 'users' => 0, 'transactions' => 0];
    if (!is_array($remote)) {
        return $stats;
    }

    $lock = fopen(DATA_DIR . '/sync.lock', 'c');
    if ($lock) {
        flock($lock, LOCK_EX);
    }

    try {
        $stats['articles'] = syncMergeArticles($remote['articles'] ?? [], $remote['deleted_articles'] ?? []);
        $stats['layouts'] = syncMergeLayouts($remote['layouts'] ?? []);
        $stats['users'] = syncMergeUsers($remote['users'] ?? []);
        $stats['transactions'] = syncMergeTransactions($remote['transactions'] ?? []);
    } finally {
        if ($lock) {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
    return $stats;
}

function syncMergeArticles($remoteArticles, $remoteDeleted) {
    $local = file_exists(ARTICLES_FILE) ? (json_decode(file_get_contents(ARTICLES_FILE), true) ?: []) : [];
    $deleted = getDeletedArticles();
    $changed = 0;

    // Merge tombstones (keep the latest deletion time)
    foreach ((array) $remoteDeleted as $id => $time) {
        if (!is_string($time)) {
            continue;
        }
        if (!isset($deleted[$id]) || strcmp($time, $deleted[$id]) > 0) {
            $deleted[$id] = $time;
        }
    }

    // Take remote articles that are newer than the local version
    foreach ((array) $remoteArticles as $id => $article) {
        if (!syncIsValidArticle($article) || $article['id'] !== $id) {
            continue;
        }
        $remoteTime = (string) ($article['updated_at'] ?? $article['created_at'] ?? '');
        if (isset($deleted[$id]) && strcmp($deleted[$id], $remoteTime) >= 0) {
            continue; // deleted later than this version was changed
        }
        $localTime = isset($local[$id]) ? (string) ($local[$id]['updated_at'] ?? $local[$id]['created_at'] ?? '') : null;
        if ($localTime === null || strcmp($remoteTime, $localTime) > 0) {
            $local[$id] = [
                'id' => $id,
                'name' => (string) $article['name'],
                'price' => (float) $article['price'],
                'color' => (string) $article['color'],
                'created_at' => (string) ($article['created_at'] ?? ''),
                'updated_at' => $remoteTime,
            ];
            $changed++;
        }
    }

    // Apply deletions that are newer than the article's last change
    foreach ($deleted as $id => $time) {
        if (isset($local[$id])) {
            $articleTime = (string) ($local[$id]['updated_at'] ?? $local[$id]['created_at'] ?? '');
            if (strcmp($time, $articleTime) >= 0) {
                unset($local[$id]);
                $changed++;
            }
        }
    }

    file_put_contents(ARTICLES_FILE, json_encode($local, JSON_PRETTY_PRINT), LOCK_EX);
    saveDeletedArticles($deleted);
    return $changed;
}

function syncMergeLayouts($remoteLayouts) {
    $changed = 0;
    foreach ((array) $remoteLayouts as $name => $layout) {
        if (!is_string($name) || !syncIsSafeName($name) || !is_array($layout) || !isset($layout['data'])) {
            continue;
        }
        $file = LAYOUTS_DIR . $name . '.json';
        $remoteTime = (int) ($layout['updated_at'] ?? 0);
        if (!file_exists($file) || $remoteTime > filemtime($file)) {
            file_put_contents($file, json_encode($layout['data'], JSON_PRETTY_PRINT), LOCK_EX);
            if ($remoteTime > 0) {
                touch($file, $remoteTime);
            }
            $changed++;
        }
    }
    return $changed;
}

function syncMergeUsers($remoteUsers) {
    $users = file_exists(USERS_FILE) ? (json_decode(file_get_contents(USERS_FILE), true) ?: []) : [];
    $known = [];
    foreach ($users as $user) {
        $known[strtolower(trim($user['username'] ?? ''))] = true;
    }
    $changed = 0;
    foreach ((array) $remoteUsers as $user) {
        if (!is_array($user) || !isset($user['username'], $user['password']) || !is_array($user['permissions'] ?? null)) {
            continue;
        }
        $key = strtolower(trim((string) $user['username']));
        if ($key === '' || isset($known[$key])) {
            continue;
        }
        $users[] = [
            'username' => $key,
            'password' => (string) $user['password'],
            'permissions' => array_values(array_filter($user['permissions'], 'is_string')),
        ];
        $known[$key] = true;
        $changed++;
    }
    if ($changed) {
        file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
    }
    return $changed;
}

function syncMergeTransactions($remoteTransactions) {
    $local = syncLoadTransactions();
    $changed = 0;
    foreach ((array) $remoteTransactions as $t) {
        if (!is_array($t) || !isset($t['id'], $t['items'], $t['total'], $t['payment_method'], $t['timestamp'])
            || !preg_match('/^[a-f0-9]{8,64}$/i', (string) $t['id'])) {
            continue;
        }
        $id = $t['id'];
        if (isset($local[$id])) {
            // Cancellation wins on both sides
            if (!empty($t['cancelled']) && empty($local[$id]['data']['cancelled'])) {
                $data = $local[$id]['data'];
                $data['cancelled'] = true;
                file_put_contents($local[$id]['file'], json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
                $changed++;
            }
            continue;
        }
        $time = strtotime((string) $t['timestamp']) ?: time();
        $data = [
            'id' => $id,
            'items' => is_array($t['items']) ? $t['items'] : [],
            'total' => (float) $t['total'],
            'payment_method' => (string) $t['payment_method'],
            'timestamp' => (string) $t['timestamp'],
            'layout' => (string) ($t['layout'] ?? ''),
            'cancelled' => !empty($t['cancelled']),
        ];
        file_put_contents(TRANSACTIONS_DIR . $time . '_' . $id . '.json', json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        $changed++;
    }
    return $changed;
}

function syncIsValidArticle($a) {
    return is_array($a)
        && isset($a['id'], $a['name'], $a['price'], $a['color'])
        && is_string($a['id']) && is_string($a['name']) && is_numeric($a['price'])
        && is_string($a['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $a['color']);
}

/** Layout names are used as file names: no paths allowed */
function syncIsSafeName($name) {
    return is_string($name) && $name !== '' && strlen($name) <= 100
        && strpos($name, '/') === false && strpos($name, '\\') === false
        && strpos($name, '..') === false && strpos($name, "\0") === false;
}

// ------------------------------------------------------------ credentials

/** Returns the user record if username/password belong to an admin, else null */
function syncCheckAdminCredentials($username, $password) {
    $users = file_exists(USERS_FILE) ? (json_decode(file_get_contents(USERS_FILE), true) ?: []) : [];
    $username = strtolower(trim((string) $username));
    foreach ($users as $user) {
        if (strtolower(trim($user['username'] ?? '')) === $username
            && is_string($password) && hash_equals((string) ($user['password'] ?? ''), $password)
            && in_array('admin', $user['permissions'] ?? [], true)) {
            return $user;
        }
    }
    return null;
}

function syncGetUserPassword($username) {
    $users = file_exists(USERS_FILE) ? (json_decode(file_get_contents(USERS_FILE), true) ?: []) : [];
    foreach ($users as $user) {
        if (strtolower(trim($user['username'] ?? '')) === strtolower(trim((string) $username))) {
            return (string) ($user['password'] ?? '');
        }
    }
    return null;
}

// ---------------------------------------------------------------- settings

function syncGetSettings() {
    if (!file_exists(SYNC_FILE)) {
        return ['remote_url' => '', 'last_sync' => null, 'last_result' => null];
    }
    return array_merge(
        ['remote_url' => '', 'last_sync' => null, 'last_result' => null],
        json_decode(file_get_contents(SYNC_FILE), true) ?: []
    );
}

function syncSaveSettings($settings) {
    return file_put_contents(SYNC_FILE, json_encode($settings, JSON_PRETTY_PRINT), LOCK_EX);
}

/** Normalises the entered URL to the base URL of the other instance */
function syncNormalizeUrl($url) {
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'http://' . $url;
    }
    // Allow pasting any page of the other instance (e.g. .../admin.php)
    $url = preg_replace('#/(api/sync|admin|register|reports|index|login)\.php.*$#i', '', $url);
    $url = rtrim($url, '/');
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
}

// ------------------------------------------------------------------ client

/**
 * Run a full two-way synchronisation with the given remote instance.
 * Returns ['ok' => bool, 'error' => string|null, 'pushed' => stats, 'pulled' => stats]
 * 'error' is a translation key (with optional 'detail').
 */
function syncWithRemote($baseUrl, $username, $password) {
    $payload = json_encode([
        'username' => $username,
        'password' => $password,
        'snapshot' => syncBuildSnapshot(),
    ]);

    $response = syncHttpPost($baseUrl . '/api/sync.php', $payload);
    if ($response['error'] !== null) {
        return ['ok' => false, 'error' => 'sync_error_connection', 'detail' => $response['error']];
    }
    $data = json_decode($response['body'], true);
    if ($response['status'] === 401 || $response['status'] === 403) {
        return ['ok' => false, 'error' => 'sync_error_auth', 'detail' => null];
    }
    if (!is_array($data)) {
        return ['ok' => false, 'error' => 'sync_error_response', 'detail' => 'HTTP ' . $response['status']];
    }
    if (empty($data['success'])) {
        return ['ok' => false, 'error' => 'sync_error_remote', 'detail' => (string) ($data['error'] ?? '')];
    }

    $pulled = syncMergeSnapshot($data['snapshot'] ?? null);
    return ['ok' => true, 'error' => null, 'pushed' => $data['merged'] ?? [], 'pulled' => $pulled];
}

function syncHttpPost($url, $body) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 120,
        ]);
        $result = curl_exec($ch);
        $error = $result === false ? curl_error($ch) : null;
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return ['status' => $status, 'body' => (string) $result, 'error' => $error];
    }

    $context = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content' => $body,
        'timeout' => 120,
        'ignore_errors' => true,
        'follow_location' => 0,
    ]]);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        $err = error_get_last();
        return ['status' => 0, 'body' => '', 'error' => $err['message'] ?? 'request failed'];
    }
    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) {
            $status = (int) $m[1];
        }
    }
    return ['status' => $status, 'body' => $result, 'error' => null];
}
