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

// Transactions directory
define('TRANSACTIONS_DIR', DATA_DIR . '/transactions/');

// Create transactions directory if it doesn't exist
if (!is_dir(TRANSACTIONS_DIR)) {
    mkdir(TRANSACTIONS_DIR, 0755, true);
}

// Layouts directory
define('LAYOUTS_DIR', DATA_DIR . '/layouts/');

// Create layouts directory if it doesn't exist
if (!is_dir(LAYOUTS_DIR)) {
    mkdir(LAYOUTS_DIR, 0755, true);
}

// Demo mode - disabled for production
define('DEMO_MODE', false);

// Multi-user demo mode - disabled for production
define('MULTI_USER_DEMO', false);

// Transactions file
define('TRANSACTIONS_FILE', DATA_DIR . '/transactions.json');

// Articles file
define('ARTICLES_FILE', DATA_DIR . '/articles.json');

// Users file
define('USERS_FILE', DATA_DIR . '/users.json');

// Initialize articles file if it doesn't exist
if (!file_exists(ARTICLES_FILE)) {
    // Empty articles for production
    file_put_contents(ARTICLES_FILE, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
}

// Initialize transactions directory
if (!is_dir(TRANSACTIONS_DIR)) {
    mkdir(TRANSACTIONS_DIR, 0755, true);
}

// Initialize users file if it doesn't exist
if (!file_exists(USERS_FILE)) {
    // Default users for production
    $defaultUsers = [
        [
            'username' => 'admin',
            'password' => 'register123',
            'permissions' => ['admin', 'user']
        ]
    ];
    file_put_contents(USERS_FILE, json_encode($defaultUsers, JSON_PRETTY_PRINT), LOCK_EX);
}
