<?php
/**
 * Payment Controller - API for processing payments
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../transactions.php';

class PaymentController extends Controller {
    
    public function process() {
        $auth = new AuthController();
        $auth->checkAuth();
        
        // Only accept POST requests
        if (!$this->isPost()) {
            $this->json(['success' => false, 'error' => $this->t('method_not_allowed', 'Method not allowed')], 405);
        }
        
        $input = $this->getJsonInput();
        
        if ($input === null) {
            $this->json(['success' => false, 'error' => $this->t('invalid_json', 'Invalid JSON input')], 400);
        }
        
        // Validate required fields
        if (!isset($input['items']) || !is_array($input['items']) || empty($input['items'])) {
            $this->json(['success' => false, 'error' => $this->t('no_items_in_cart', 'No items in cart')], 400);
        }
        
        if (!isset($input['method']) || !in_array($input['method'], ['cash', 'card'])) {
            $this->json(['success' => false, 'error' => $this->t('invalid_payment_method', 'Invalid payment method')], 400);
        }
        
        $total = isset($input['total']) ? (float) $input['total'] : 0;
        $method = $input['method'];
        $items = $input['items'];
        $layout = isset($input['layout']) ? $input['layout'] : '';
        $timestamp = isset($input['timestamp']) ? (int) $input['timestamp'] : time();
        // Log the transaction
        try {
            $transactionId = logTransaction($items, $total, $method, $layout, $timestamp);
            $this->json([
                'success' => true,
                'transaction_id' => $transactionId,
                'message' => $this->t('payment_processed', 'Payment processed successfully')
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $this->t('transaction_log_failed', 'Failed to log transaction')], 500);
        }
    }
}
