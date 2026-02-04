<!DOCTYPE html>
<html lang="<?php echo $lang->getLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__('admin_title'); ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg">
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
    <link rel="stylesheet" href="views/common.css">
    <style>
        .container {
            max-width: 1000px;
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
            background: #16213e;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }
        .card h2 {
            margin-bottom: 20px;
            color: #4ecca3;
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
            color: #4ecca3;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #0f3460;
            border-radius: 5px;
            font-size: 1em;
            background: #1a1a2e;
            color: white;
        }
        .form-group input[type="color"] {
            height: 42px;
            cursor: pointer;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #4ecca3;
            color: #1a1a2e;
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
            border-bottom: 1px solid #0f3460;
        }
        .articles-table th {
            background: #0f3460;
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
    <?php include '_language_selector.php'; ?>
    <div class="header">
        <h1>⚙️ <?php echo $__('admin'); ?></h1>
        <div class="header-links">
            <a href="register.php">🛒 <?php echo $__('register'); ?></a>
            <a href="reports.php">📊 <?php echo $__('reports'); ?></a>
            <a href="index.php">🏠 <?php echo $__('home'); ?></a>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($message)): ?>
        <div class="message <?php echo View::escape($messageType); ?>">
            <?php echo View::escape($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?php echo $__('add_new_article'); ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><?php echo $__('article_name'); ?></label>
                        <input type="text" id="name" name="name" required placeholder="e.g., Coffee">
                    </div>
                    <div class="form-group">
                        <label for="price"><?php echo $__('price'); ?> (€)</label>
                        <input type="number" id="price" name="price" step="0.01" min="-5.00" required placeholder="2.50">
                    </div>
                    <div class="form-group">
                        <label for="color"><?php echo $lang->get('button_color'); ?></label>
                        <input type="color" id="color" name="color" value="#007bff">
                    </div>
                </div>
                <button type="submit" class="btn-success">➕ Add Article</button>
            </form>
        </div>
        
        <div class="card">
            <h2><?php echo $__('manage_articles'); ?></h2>
            <?php if (empty($articles)): ?>
            <div class="no-articles">
                <p><?php echo $__('no_articles_yet'); ?></p>
            </div>
            <?php else: ?>
            <table class="articles-table">
                <thead>
                    <tr>
                        <th><?php echo $__('name'); ?></th>
                        <th>Price</th>
                        <th>Color</th>
                        <th><?php echo $lang->get('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                        <tr>
                            <input type="hidden" name="id" value="<?php echo View::escape($article->id); ?>">
                            <td>
                                <input type="text" name="name" value="<?php echo View::escape($article->name); ?>" required>
                            </td>
                            <td>
                                <input type="number" name="price" value="<?php echo View::escape($article->price); ?>" step="0.01" min="0.01" required style="width: 100px;"> €
                            </td>
                            <td>
                                <input type="color" name="color" value="<?php echo View::escape($article->color ?? '#007bff'); ?>">
                            </td>
                            <td class="actions">
                                <button type="submit" name="action" value="update" class="btn-primary">💾 <?php echo $lang->get('save'); ?></button>
                                <button type="submit" name="action" value="delete" class="btn-danger" onclick="return confirm('<?php echo $lang->get('are_you_sure'); ?>')">🗑️ <?php echo $lang->get('delete'); ?></button>
                            </td>
                        </tr>
                    </form>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2><?php echo $lang->get('add_user'); ?></h2>
            <form method="post" class="form-inline">
                <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username"><?php echo $lang->get('username'); ?>:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo $lang->get('password'); ?>:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password"><?php echo $lang->get('confirm_password'); ?>:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <button type="submit" name="action" value="add_user" class="btn-success">➕ <?php echo $lang->get('add_user_button'); ?></button>
            </form>
        </div>
        
        <div class="card">
            <h2><?php echo $lang->get('existing_users'); ?></h2>
            <?php if (empty($users)): ?>
            <div class="no-articles">
                <p><?php echo $lang->get('no_users_yet'); ?></p>
            </div>
            <?php else: ?>
            <table class="articles-table">
                <thead>
                    <tr>
                        <th><?php echo $lang->get('username'); ?></th>
                        <th><?php echo $lang->get('permissions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo View::escape($user['username']); ?></td>
                        <td><?php echo View::escape(implode(', ', $user['permissions'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2><?php echo $lang->get('backup_restore'); ?></h2>
            <div class="form-row">
                <div class="form-group">
                    <label><?php echo $lang->get('download_backup'); ?></label>
                    <p style="color: #ccc; margin: 5px 0;">
                        <?php echo $lang->get('download_backup_description'); ?></p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="backup">
                        <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                        <button type="submit" class="btn-primary">📥 <?php echo $lang->get('download_backup_button'); ?></button>
                    </form>
                </div>
                <div class="form-group">
                    <label for="backup_file"><?php echo $lang->get('upload_restore'); ?></label>
                    <p style="color: #ccc; margin: 5px 0;">
                        <?php echo $lang->get('upload_backup_description'); ?></p>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="restore">
                        <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                        <input type="file" id="backup_file" name="backup_file" accept=".zip" required style="margin-bottom: 10px;">
                        <br>
                        <button type="submit" class="btn-success">📤 <?php echo $lang->get('restore_from_backup'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
