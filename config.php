<?php
/**
 * Configuration file for Simple Register
 */

// Authentication credentials
define('AUTH_USER', 'admin');
define('AUTH_PASS', 'register123');

// Data directory
define('DATA_DIR', __DIR__ . '/data');

// Articles file
define('ARTICLES_FILE', DATA_DIR . '/articles.json');

// Transactions log file
define('TRANSACTIONS_FILE', DATA_DIR . '/transactions.json');

// Ensure data directory exists
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Initialize articles file if it doesn't exist
if (!file_exists(ARTICLES_FILE)) {
    file_put_contents(ARTICLES_FILE, json_encode([], JSON_PRETTY_PRINT));
}

// Initialize transactions file if it doesn't exist
if (!file_exists(TRANSACTIONS_FILE)) {
    file_put_contents(TRANSACTIONS_FILE, json_encode([], JSON_PRETTY_PRINT));
}
