<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Simple Register</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #1a1a2e;
            color: white;
            min-height: 100vh;
        }
        .header {
            background: #16213e;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 1.3em;
        }
        .header-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            margin-left: 10px;
        }
        .header-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
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
        .section {
            background: #16213e;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section h2 {
            margin-bottom: 15px;
            color: #4ecca3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #0f3460;
        }
        th {
            background: #0f3460;
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
    <div class="header">
        <h1>📊 Sales Reports</h1>
        <div class="header-links">
            <a href="register.php">🛒 Register</a>
            <a href="admin.php">⚙️ Admin</a>
            <a href="index.php">🏠 Home</a>
        </div>
    </div>
    
    <div class="container">
        <div class="filters">
            <form method="GET">
                <label>From:</label>
                <input type="date" name="start_date" value="<?php echo View::escape($startDate); ?>">
                <label>To:</label>
                <input type="date" name="end_date" value="<?php echo View::escape($endDate); ?>">
                <button type="submit">🔍 Filter</button>
            </form>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_transactions']); ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <?php foreach ($stats['methods'] as $method => $data): ?>
            <div class="stat-card">
                <div class="stat-value">€<?php echo number_format($data['revenue'], 2); ?></div>
                <div class="stat-label"><?php echo ucfirst($method); ?> Revenue</div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section">
            <h2>Article Statistics</h2>
            <table>
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articleStats as $article): ?>
                    <tr>
                        <td><?php echo View::escape($article['article_name']); ?></td>
                        <td><?php echo (int)$article['total_qty']; ?></td>
                        <td>€<?php echo number_format($article['total_revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>Recent Transactions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentTransactions as $transaction): ?>
                    <tr>
                        <td><?php echo View::escape($transaction['timestamp']); ?></td>
                        <td>
                            <?php 
                            $items = $transaction['items'];
                            echo implode(', ', array_map(function($item) {
                                return $item['name'] . ' (×' . $item['qty'] . ')';
                            }, $items));
                            ?>
                        </td>
                        <td>€<?php echo number_format($transaction['total'], 2); ?></td>
                        <td>
                            <span class="payment-method <?php echo $transaction['payment_method']; ?>">
                                <?php echo ucfirst($transaction['payment_method']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>