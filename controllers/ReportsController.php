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
        
        // Handle form submissions
        if ($this->isPost()) {
            $this->handlePost();
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
        $data = [
            'stats' => $stats,
            'articleStats' => $articleStats,
            'recentTransactions' => $filteredTransactions,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        if (isset($_SESSION['error'])) {
            $data['error'] = $_SESSION['error'];
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['message'])) {
            $data['message'] = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        $this->render('reports', $data);
    }
    
    private function handlePost() {
        if (!$this->validateCsrf()) {
            // Invalid CSRF, do not regenerate, allow retry
            $this->redirect('reports.php?' . http_build_query($_GET));
            return;
        }
        // Valid CSRF, now handle action
        $action = $this->post('action', '');
        
        if ($action === 'cancel_transaction') {
            $id = $this->post('transaction_id', '');
            $log = "Cancel request for ID: $id\n";
            list($success, $details) = cancelTransaction($id);
            $log .= $details;
            if ($success) {
                // Success, regenerate token
                $this->regenerateCsrf();
                $_SESSION['message'] = 'Transaction cancelled successfully';
                $this->redirect('reports.php?' . http_build_query($_GET));
            } else {
                // Failure, do not regenerate, allow retry
                $_SESSION['error'] = 'Failed to cancel transaction: ' . str_replace("\n", "<br>", $log);
                $this->redirect('reports.php?' . http_build_query($_GET));
            }
        }
    }
}