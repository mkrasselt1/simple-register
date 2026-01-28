<?php
/**
 * Reports Controller - Sales reports and analytics
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../transactions.php';

class ReportsController extends Controller {
    
    public function index() {
        $auth = new AuthController();
        $auth->checkAuth();
        if (!in_array('admin', $_SESSION['user']['permissions'])) {
            $this->redirect('register.php');
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $stats = getTransactionStats($startDate . ' 00:00:00', $endDate . ' 23:59:59');
        $articleStats = getArticleStats($startDate . ' 00:00:00', $endDate . ' 23:59:59');
        $transactions = getTransactions();
        $filteredTransactions = array_filter($transactions, function($transaction) use ($startDate, $endDate) {
            $timestamp = strtotime($transaction->getTimestamp());
            return $timestamp >= strtotime($startDate . ' 00:00:00') && $timestamp <= strtotime($endDate . ' 23:59:59');
        });
        $this->render('reports', [
            'stats' => $stats,
            'articleStats' => $articleStats,
            'recentTransactions' => $filteredTransactions,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}