<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__('welcome_title'); ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg">
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
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
        .container {
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .nav-links {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .nav-link {
            display: inline-block;
            padding: 20px 40px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.2em;
            transition: background 0.3s;
        }
        .nav-link:hover {
            background: #0056b3;
        }
        .nav-link.admin {
            background: #28a745;
        }
        .nav-link.admin:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <?php include '_language_selector.php'; ?>
    <div class="container">
        <h1><?php echo $__('welcome_title'); ?></h1>
        <p style="color: #666; margin-bottom: 40px;"><?php echo $__('welcome_subtitle'); ?></p>
        <div class="nav-links">
            <a href="register.php" class="nav-link">📋 <?php echo $__('go_to_register'); ?></a>
            <?php if (isset($_SESSION['user']) && in_array('admin', $_SESSION['user']['permissions'])): ?>
            <a href="admin.php" class="nav-link admin">⚙️ <?php echo $__('go_to_admin'); ?></a>
            <?php endif; ?>
            <a href="logout.php" class="nav-link">🚪 <?php echo $__('logout'); ?></a>
        </div>
    </div>
</body>
</html>
