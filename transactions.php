<?php
/**
 * Transactions management functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/entities/Transaction.php';

define('TRANSACTIONS_DIR', __DIR__ . '/data/transactions/');

function getTransactions() {
    if (!is_dir(TRANSACTIONS_DIR)) {
        return [];
    }
    $files = glob(TRANSACTIONS_DIR . '*.json');
    $result = [];
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $t = json_decode($content, true);
        if ($t === null && json_last_error() !== JSON_ERROR_NONE) {
            // Skip corrupted files
            continue;
        }
        $result[] = new Transaction(
            $t['id'],
            $t['items'],
            $t['total'],
            $t['payment_method'],
            $t['timestamp'],
            $t['layout'] ?? '',
            $t['cancelled'] ?? false
        );
    }
    // Nach Zeit sortieren (neueste zuerst)
    usort($result, function($a, $b) {
        return strtotime($b->getTimestamp()) <=> strtotime($a->getTimestamp());
    });
    return $result;
}

// Nicht mehr benötigt, da jede Transaktion einzeln gespeichert wird
function saveTransactions($transactions) {
    return true;
}

function logTransaction($items, $total, $paymentMethod, $layout = '') {
    if (!is_dir(TRANSACTIONS_DIR)) {
        mkdir(TRANSACTIONS_DIR, 0777, true);
    }
    $timestamp = time();
    $id = bin2hex(random_bytes(8));
    $data = [
        'id' => $id,
        'items' => $items,
        'total' => (float) $total,
        'payment_method' => $paymentMethod,
        'timestamp' => date('Y-m-d H:i:s', $timestamp),
        'layout' => $layout,
        'cancelled' => false
    ];
    $filename = TRANSACTIONS_DIR . $timestamp . '_' . $id . '.json';
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    return $id;
}

function getTransactionStats($startDate = null, $endDate = null) {
    $transactions = getTransactions();
    $stats = [];
    $totalRevenue = 0;
    $totalCount = 0;
    
    foreach ($transactions as $transaction) {
        if ($transaction->isCancelled()) continue;
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
        if ($transaction->isCancelled()) continue;
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

function cancelTransaction($id) {
    $log = "Attempting to cancel transaction: $id\n";
    if (!is_dir(TRANSACTIONS_DIR)) {
        $log .= "TRANSACTIONS_DIR does not exist: " . TRANSACTIONS_DIR . "\n";
        return [false, $log];
    }
    $files = glob(TRANSACTIONS_DIR . '*.json');
    $log .= "Found " . count($files) . " transaction files\n";
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $t = json_decode($content, true);
        if ($t === null) {
            $log .= "JSON decode failed for $file: " . json_last_error_msg() . "\n";
            continue;
        }
        $log .= "Checking file: $file, ID: " . ($t['id'] ?? 'none') . "\n";
        if (!$t || $t['id'] !== $id) continue;
        $t['cancelled'] = true;
        $result = file_put_contents($file, json_encode($t, JSON_PRETTY_PRINT), LOCK_EX);
        $log .= "Updated file: $file, result: $result\n";
        return [$result !== false, $log];
    }
    $log .= "Transaction $id not found\n";
    return [false, $log];
}
