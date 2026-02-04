<!DOCTYPE html>
<html lang="<?php echo $lang->getLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__('login_title'); ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg">
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
    <link rel="stylesheet" href="views/common.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: #16213e;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h1 {
            margin-bottom: 20px;
            color: #4ecca3;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            text-align: left;
        }
        input {
            margin-bottom: 15px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #0f3460;
            color: white;
        }
        button {
            padding: 10px;
            background: #4ecca3;
            color: #1a1a2e;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover {
            background: #3bbd8c;
        }
        p {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '_language_selector.php'; ?>
    <div class="container">
        <h1><?php echo $__('login'); ?></h1>
        <?php if (isset($data['error'])): ?>
            <p style="color: red;"><?php echo View::escape($data['error']); ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo View::escape($data['csrf_token'] ?? ''); ?>">
            <label for="username"><?php echo $__('username'); ?>:</label>
            <input type="text" id="username" name="username" required>
            <label for="password"><?php echo $__('password'); ?>:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit"><?php echo $__('login_button'); ?></button>
        </form>
    </div>
</body>
</html>