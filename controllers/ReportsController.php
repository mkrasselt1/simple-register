<?php
/**
 * Reports Controller - Sales reports and analytics
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../transactions.php';

class ReportsController extends Controller {
    
    public function index() {
        authenticate();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $stats = getTransactionStats($startDate . ' 00:00:00', $endDate . ' 23:59:59');
        $articleStats = getArticleStats($startDate . ' 00:00:00', $endDate . ' 23:59:59');
        $transactions = getTransactions();
        $recentTransactions = array_slice($transactions, 0, 50);
        $recentTransactionsArray = array_map(function($transaction) {
            return $transaction->toArray();
        }, $recentTransactions);
        
        $this->render('reports', [
            'stats' => $stats,
            'articleStats' => $articleStats,
            'recentTransactions' => $recentTransactionsArray,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}