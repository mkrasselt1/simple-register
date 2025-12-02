<?php
/**
 * Transactions management functions
 */

require_once __DIR__ . '/config.php';

function getTransactions() {
    if (!file_exists(TRANSACTIONS_FILE)) {
        return [];
    }
    $content = file_get_contents(TRANSACTIONS_FILE);
    return json_decode($content, true) ?: [];
}

function saveTransactions($transactions) {
    return file_put_contents(TRANSACTIONS_FILE, json_encode($transactions, JSON_PRETTY_PRINT));
}

function logTransaction($items, $total, $paymentMethod) {
    $transactions = getTransactions();
    $id = uniqid();
    $transactions[] = [
        'id' => $id,
        'items' => $items,
        'total' => (float) $total,
        'payment_method' => $paymentMethod,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    saveTransactions($transactions);
    return $id;
}
