<?php
/**
 * Transactions management functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/entities/Transaction.php';

function getTransactions() {
    if (!file_exists(TRANSACTIONS_FILE)) {
        return [];
    }
    $content = file_get_contents(TRANSACTIONS_FILE);
    $arr = json_decode($content, true) ?: [];
    $result = [];
    foreach ($arr as $t) {
        $result[] = new Transaction(
            $t['id'],
            $t['items'],
            $t['total'],
            $t['payment_method'],
            $t['timestamp'],
            $t['layout'] ?? ''
        );
    }
    return $result;
}

function saveTransactions($transactions) {
    return file_put_contents(TRANSACTIONS_FILE, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
}

function logTransaction($items, $total, $paymentMethod, $layout = '') {
    $transactions = getTransactions();
    $id = bin2hex(random_bytes(8));
    $transactions[] = [
        'id' => $id,
        'items' => $items,
        'total' => (float) $total,
        'payment_method' => $paymentMethod,
        'timestamp' => date('Y-m-d H:i:s'),
        'layout' => $layout
    ];
    saveTransactions($transactions);
    return $id;
}

function getTransactionStats($startDate = null, $endDate = null) {
    $transactions = getTransactions();
    $stats = [];
    $totalRevenue = 0;
    $totalCount = 0;
    
    foreach ($transactions as $transaction) {
        $timestamp = strtotime($transaction->getTimestamp());
        if ($startDate && $timestamp < strtotime($startDate)) continue;
        if ($endDate && $timestamp > strtotime($endDate)) continue;
        $method = $transaction->getPaymentMethod();
        if (!isset($stats[$method])) {
            $stats[$method] = ['count' => 0, 'revenue' => 0];
        }
        $stats[$method]['count']++;
        $stats[$method]['revenue'] += $transaction->getTotal();
        $totalRevenue += $transaction->getTotal();
        $totalCount++;
    }
    
    return [
        'total_transactions' => $totalCount,
        'total_revenue' => $totalRevenue,
        'methods' => $stats
    ];
}

function getArticleStats($startDate = null, $endDate = null) {
    $transactions = getTransactions();
    $articleStats = [];
    foreach ($transactions as $transaction) {
        $timestamp = strtotime($transaction->getTimestamp());
        if ($startDate && $timestamp < strtotime($startDate)) continue;
        if ($endDate && $timestamp > strtotime($endDate)) continue;
        foreach ($transaction->getItems() as $item) {
            $id = $item->getId();
            if (!isset($articleStats[$id])) {
                $articleStats[$id] = [
                    'name' => $item->getName(),
                    'total_qty' => 0,
                    'total_revenue' => 0
                ];
            }
            $articleStats[$id]['total_qty'] += $item->getQty();
            $articleStats[$id]['total_revenue'] += $item->getPrice() * $item->getQty();
        }
    }
    // Sort by revenue desc
    uasort($articleStats, function($a, $b) {
        return $b['total_revenue'] <=> $a['total_revenue'];
    });
    return $articleStats;
}
