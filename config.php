<?php
/**
 * Configuration file for Simple Register
 */

session_start();

// Data directory
define('DATA_DIR', __DIR__ . '/data');

// Create data directory if it doesn't exist
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Articles file
define('ARTICLES_FILE', DATA_DIR . '/articles.json');

// Transactions file
define('TRANSACTIONS_FILE', DATA_DIR . '/transactions.json');

// Users file
define('USERS_FILE', DATA_DIR . '/users.json');

// Initialize transactions file if it doesn't exist
if (!file_exists(TRANSACTIONS_FILE)) {
    file_put_contents(TRANSACTIONS_FILE, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
}

// Initialize users file if it doesn't exist
if (!file_exists(USERS_FILE)) {
    $defaultUsers = [
        [
            'username' => 'admin',
            'password' => 'register123',
            'permissions' => ['admin', 'user']
        ]
    ];
    file_put_contents(USERS_FILE, json_encode($defaultUsers, JSON_PRETTY_PRINT), LOCK_EX);
}
