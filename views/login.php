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
        <form method="post" action="login.php" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo View::escape($data['csrf_token'] ?? ''); ?>">
            <label for="username"><?php echo $__('username'); ?>:</label>
            <input type="text" id="username" name="username" required>
            <label for="password"><?php echo $__('password'); ?>:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit"><?php echo $__('login_button'); ?></button>
        </form>
    </div>
    <script>
        const LOGIN_STORAGE_KEY = 'simple_register_last_login';

        function loadLastLogin() {
            try {
                const raw = localStorage.getItem(LOGIN_STORAGE_KEY);
                if (!raw) return;
                const data = JSON.parse(raw);
                if (data.username) {
                    document.getElementById('username').value = data.username;
                }
                if (data.password) {
                    document.getElementById('password').value = data.password;
                }
            } catch (error) {
                console.warn('Failed to load last login', error);
            }
        }

        function shouldStoreLogin(username, password) {
            const normalizedUser = (username || '').trim().toLowerCase();
            const normalizedPass = (password || '').trim();
            return !(normalizedUser === 'admin' && normalizedPass === 'admin');
        }

        document.addEventListener('DOMContentLoaded', loadLastLogin);

        document.getElementById('loginForm').addEventListener('submit', function () {
            const username = document.getElementById('username').value || '';
            const password = document.getElementById('password').value || '';

            if (shouldStoreLogin(username, password)) {
                localStorage.setItem(
                    LOGIN_STORAGE_KEY,
                    JSON.stringify({
                        username: username.trim(),
                        password: password
                    })
                );
            } else {
                localStorage.removeItem(LOGIN_STORAGE_KEY);
            }
        });
    </script>
</body>
</html>