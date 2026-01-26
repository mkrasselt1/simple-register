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
    return file_put_contents(TRANSACTIONS_FILE, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
}

function logTransaction($items, $total, $paymentMethod) {
    $transactions = getTransactions();
    $id = bin2hex(random_bytes(8));
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

function getTransactionStats($startDate = null, $endDate = null) {
    $transactions = getTransactions();
    $stats = [];
    $totalRevenue = 0;
    $totalCount = 0;
    
    foreach ($transactions as $transaction) {
        $timestamp = strtotime($transaction['timestamp']);
        if ($startDate && $timestamp < strtotime($startDate)) continue;
        if ($endDate && $timestamp > strtotime($endDate)) continue;
        
        $method = $transaction['payment_method'];
        if (!isset($stats[$method])) {
            $stats[$method] = ['count' => 0, 'revenue' => 0];
        }
        $stats[$method]['count']++;
        $stats[$method]['revenue'] += $transaction['total'];
        $totalRevenue += $transaction['total'];
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
        $timestamp = strtotime($transaction['timestamp']);
        if ($startDate && $timestamp < strtotime($startDate)) continue;
        if ($endDate && $timestamp > strtotime($endDate)) continue;
        
        foreach ($transaction['items'] as $item) {
            $id = $item['id'];
            if (!isset($articleStats[$id])) {
                $articleStats[$id] = [
                    'name' => $item['name'],
                    'qty' => 0,
                    'revenue' => 0
                ];
            }
            $articleStats[$id]['qty'] += $item['qty'];
            $articleStats[$id]['revenue'] += $item['price'] * $item['qty'];
        }
    }
    
    // Sort by revenue desc
    uasort($articleStats, function($a, $b) {
        return $b['revenue'] <=> $a['revenue'];
    });
    
    return $articleStats;
}
