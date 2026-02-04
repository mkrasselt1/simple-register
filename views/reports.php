<!DOCTYPE html>
<html lang="<?php echo $lang->getLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__('reports_title'); ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg">
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
    <link rel="stylesheet" href="views/common.css">
    <style>
        .container {
            max-width: 1200px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #16213e;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: #4ecca3;
            margin-bottom: 5px;
        }
        .stat-label {
            opacity: 0.8;
            font-size: 0.9em;
        }
        .filters {
            background: #16213e;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .filters form {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .filters label {
            font-weight: 500;
        }
        .filters input {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            background: #0f3460;
            color: white;
        }
        .filters button {
            padding: 8px 20px;
            background: #4ecca3;
            border: none;
            border-radius: 5px;
            color: #1a1a2e;
            cursor: pointer;
            font-weight: 600;
        }
        .payment-method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .cash {
            background: #4ecca3;
            color: #1a1a2e;
        }
        .card {
            background: #007bff;
            color: white;
        }
        .total-row {
            background: #0f3460;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <?php include '_language_selector.php'; ?>
    <div class="header">
        <h1>📊 <?php echo $__('reports'); ?></h1>
        <div class="header-links">
            <a href="register.php">🛒 <?php echo $__('register'); ?></a>
            <a href="admin.php">⚙️ <?php echo $__('admin'); ?></a>
            <a href="index.php">🏠 <?php echo $__('home'); ?></a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($data['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $data['error']; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($data['message'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo View::escape($data['message']); ?>
            </div>
        <?php endif; ?>
        <div class="filters">
            <form method="GET">
                <label><?php echo $__('from'); ?>:</label>
                <input type="date" name="start_date" value="<?php echo View::escape($startDate); ?>">
                <label><?php echo $__('to'); ?>:</label>
                <input type="date" name="end_date" value="<?php echo View::escape($endDate); ?>">
                <button type="submit">🔍 <?php echo $__('filter'); ?></button>
            </form>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_transactions']); ?></div>
                <div class="stat-label"><?php echo $__('total_transactions'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label"><?php echo $__('total_sales'); ?></div>
            </div>
            <?php foreach ($stats['methods'] as $method => $data): ?>
            <div class="stat-card">
                <div class="stat-value">€<?php echo number_format($data['revenue'], 2); ?></div>
                <div class="stat-label"><?php echo ucfirst($method); ?> <?php echo $__('revenue'); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section">
            <h2><?php echo $__('article_statistics'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php echo $__('article'); ?></th>
                        <th><?php echo $__('quantity_sold'); ?></th>
                        <th><?php echo $__('revenue'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articleStats as $article): ?>
                    <tr>
                        <td><?php echo View::escape($article['name']); ?></td>
                        <td><?php echo (int)$article['total_qty']; ?></td>
                        <td>€<?php echo number_format($article['total_revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2><?php echo $__('recent_transactions'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php echo $__('date_time'); ?></th>
                        <th><?php echo $__('items'); ?></th>
                        <th><?php echo $__('total'); ?></th>
                        <th><?php echo $__('payment_method'); ?></th>
                        <th><?php echo $__('layout'); ?></th>
                        <th><?php echo $__('status'); ?></th>
                        <th><?php echo $__('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentTransactions as $transaction): ?>
                    <tr<?php if ($transaction->isCancelled()): ?> style="text-decoration: line-through; opacity: 0.6;"<?php endif; ?>>
                        <td><?php echo View::escape($transaction->getTimestamp()); ?></td>
                        <td>
                            <?php 
                            $items = $transaction->getItems();
                            echo implode(', ', array_map(function($item) {
                                return $item->getName() . ' (×' . $item->getQty() . ')';
                            }, $items));
                            ?>
                        </td>
                        <td>€<?php echo number_format($transaction->getTotal(), 2); ?></td>
                        <td>
                            <span class="payment-method <?php echo $transaction->getPaymentMethod(); ?>">
                                <?php echo ucfirst($transaction->getPaymentMethod()); ?>
                            </span>
                        </td>
                        <td><?php echo View::escape($transaction->getLayout()); ?></td>
                        <td><?php echo $transaction->isCancelled() ? $__('cancelled') : $__('active'); ?></td>
                        <td>
                            <?php if (!$transaction->isCancelled()): ?>
                            <form method="post" action="reports.php" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                                <input type="hidden" name="transaction_id" value="<?php echo View::escape($transaction->getId()); ?>">
                                <button type="submit" name="action" value="cancel_transaction" class="btn-danger" onclick="return confirm('<?php echo $__('confirm_cancel_transaction'); ?>')"><?php echo $__('cancel'); ?></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>