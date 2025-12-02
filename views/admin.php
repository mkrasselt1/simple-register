<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Simple Register</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        .header {
            background: #28a745;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 1.5em;
        }
        .header a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
        }
        .header a:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 150px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        .form-group input[type="color"] {
            height: 42px;
            cursor: pointer;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: opacity 0.3s;
        }
        button:hover {
            opacity: 0.8;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .articles-table {
            width: 100%;
            border-collapse: collapse;
        }
        .articles-table th,
        .articles-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .articles-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            display: inline-block;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .no-articles {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
            }
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚙️ Admin Panel</h1>
        <div>
            <a href="register.php">📋 Register</a>
            <a href="index.php">🏠 Home</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($message)): ?>
        <div class="message <?php echo View::escape($messageType); ?>">
            <?php echo View::escape($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Article</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Article Name</label>
                        <input type="text" id="name" name="name" required placeholder="e.g., Coffee">
                    </div>
                    <div class="form-group">
                        <label for="price">Price (€)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" required placeholder="2.50">
                    </div>
                    <div class="form-group">
                        <label for="color">Button Color</label>
                        <input type="color" id="color" name="color" value="#007bff">
                    </div>
                </div>
                <button type="submit" class="btn-success">➕ Add Article</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Manage Articles</h2>
            <?php if (empty($articles)): ?>
            <div class="no-articles">
                <p>No articles yet. Add your first article above!</p>
            </div>
            <?php else: ?>
            <table class="articles-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Color</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo View::escape($article['id']); ?>">
                            <td>
                                <input type="text" name="name" value="<?php echo View::escape($article['name']); ?>" required>
                            </td>
                            <td>
                                <input type="number" name="price" value="<?php echo View::escape($article['price']); ?>" step="0.01" min="0.01" required style="width: 100px;">
                            </td>
                            <td>
                                <input type="color" name="color" value="<?php echo View::escape($article['color'] ?? '#007bff'); ?>">
                            </td>
                            <td class="actions">
                                <button type="submit" name="action" value="update" class="btn-primary">💾 Save</button>
                                <button type="submit" name="action" value="delete" class="btn-danger" onclick="return confirm('Are you sure?')">🗑️ Delete</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
