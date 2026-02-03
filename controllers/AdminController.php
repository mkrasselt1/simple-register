<?php

/**
 * Admin Controller - Manages Articles
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../articles.php';

class AdminController extends Controller
{

    private $message = '';
    private $messageType = '';

    public function index()
    {
        $auth = new AuthController();
        $auth->checkAuth();
        if (!in_array('admin', $_SESSION['user']['permissions'])) {
            $this->redirect('register.php');
        }

        // Handle form submissions
        if ($this->isPost()) {
            $this->handlePost();
        }

        $articles = getArticles();
        $users = $this->getUsers();

        $this->render('admin', [
            'articles' => $articles,
            'users' => $users,
            'message' => $this->message,
            'messageType' => $this->messageType
        ]);
    }

    private function handlePost()
    {
        if (!$this->validateCsrf()) {
            $this->message = 'Invalid request';
            $this->messageType = 'error';
            return;
        }
        // Valid, now handle
        $action = $this->post('action', '');

        switch ($action) {
            case 'add':
                if ($this->addArticle()) {
                    $this->regenerateCsrf();
                }
                break;
            case 'update':
                if ($this->updateArticle()) {
                    $this->regenerateCsrf();
                }
                break;
            case 'delete':
                if ($this->deleteArticle()) {
                    $this->regenerateCsrf();
                }
                break;
            case 'add_user':
                if ($this->addUser()) {
                    $this->regenerateCsrf();
                }
                break;
            case 'backup':
                $this->downloadBackup();
                break;
            case 'restore':
                if ($this->uploadRestore()) {
                    $this->regenerateCsrf();
                }
                break;
        }
    }

    private function addArticle()
    {
        $name = trim($this->post('name', ''));
        $price = floatval($this->post('price', 0));
        $color = $this->post('color', '#007bff');

        if ($name && $price > 0) {
            addArticle($name, $price, $color);
            $this->message = 'Article added successfully!';
            $this->messageType = 'success';
        } else {
            $this->message = 'Please provide a valid name and price.';
            $this->messageType = 'error';
        }
    }

    private function updateArticle()
    {
        $id = $this->post('id', '');
        $name = trim($this->post('name', ''));
        $price = floatval($this->post('price', 0));
        $color = $this->post('color', '#007bff');

        if ($id && $name && $price > 0) {
            if (updateArticle($id, $name, $price, $color)) {
                $this->message = 'Article updated successfully!';
                $this->messageType = 'success';
            } else {
                $this->message = 'Article not found.';
                $this->messageType = 'error';
            }
        } else {
            $this->message = 'Please provide valid data.';
            $this->messageType = 'error';
        }
    }

    private function deleteArticle()
    {
        $id = $this->post('id', '');

        if ($id && deleteArticle($id)) {
            $this->message = 'Article deleted successfully!';
            $this->messageType = 'success';
        } else {
            $this->message = 'Article not found.';
            $this->messageType = 'error';
        }
    }

    private function addUser()
    {
        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');
        $confirmPassword = $this->post('confirm_password', '');

        if ($username && $password) {
            if ($password !== $confirmPassword) {
                $this->message = 'Passwords do not match.';
                $this->messageType = 'error';
                return;
            }
            $users = $this->getUsers();
            // Check if user exists
            foreach ($users as $user) {
                if ($user['username'] === $username) {
                    $this->message = 'User already exists.';
                    $this->messageType = 'error';
                    return;
                }
            }
            $users[] = [
                'username' => $username,
                'password' => $password,
                'permissions' => ['user'] // default
            ];
            file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
            $this->message = 'User added successfully!';
            $this->messageType = 'success';
        } else {
            $this->message = 'Please provide username and password.';
            $this->messageType = 'error';
        }
    }

    private function getUsers()
    {
        $usersFile = USERS_FILE;
        if (!file_exists($usersFile)) {
            return [];
        }
        $data = json_decode(file_get_contents($usersFile), true);
        return $data ?: [];
    }

    private function downloadBackup()
    {
        $files = [
            'data/articles.json',
            'data/transactions.json',
            'data/users.json',
            'layouts/default.json'
        ];

        // Add all transaction files
        $transactionDir = __DIR__ . '/../transactions';
        if (is_dir($transactionDir)) {
            $transactionFiles = glob($transactionDir . '/*.json');
            foreach ($transactionFiles as $file) {
                $files[] = 'transactions/' . basename($file);
            }
        }

        $zip = new ZipArchive();
        $zipFile = tempnam(sys_get_temp_dir(), 'backup') . '.zip';

        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            foreach ($files as $file) {
                $fullPath = __DIR__ . '/../' . $file;
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $file);
                }
            }
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="simple-register-backup-' . date('Y-m-d-H-i-s') . '.zip"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);
            unlink($zipFile);
            exit;
        } else {
            $this->message = 'Failed to create backup.';
            $this->messageType = 'error';
        }
    }

    private function uploadRestore()
    {
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            $this->message = 'No file uploaded or upload error.';
            $this->messageType = 'error';
            return false;
        }

        $file = $_FILES['backup_file'];
        $allowedTypes = ['application/zip', 'application/x-zip-compressed'];

        if (!in_array($file['type'], $allowedTypes) && !preg_match('/\.zip$/i', $file['name'])) {
            $this->message = 'Only ZIP files are allowed.';
            $this->messageType = 'error';
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) === true) {
            $extractPath = __DIR__ . '/../temp_restore_' . time();
            mkdir($extractPath, 0755, true);

            $zip->extractTo($extractPath);
            $zip->close();

            // Validate and move files
            $filesToRestore = [
                'data/articles.json' => ARTICLES_FILE,
                'data/transactions.json' => TRANSACTIONS_FILE,
                'data/users.json' => USERS_FILE,
                'layouts/default.json' => __DIR__ . '/../layouts/default.json'
            ];

            $success = true;
            foreach ($filesToRestore as $zipPath => $targetPath) {
                $sourcePath = $extractPath . '/' . $zipPath;
                if (file_exists($sourcePath)) {
                    if (!copy($sourcePath, $targetPath)) {
                        $success = false;
                        break;
                    }
                }
            }

            // Restore transaction files
            $transactionSourceDir = $extractPath . '/transactions';
            $transactionTargetDir = __DIR__ . '/../transactions';
            if (is_dir($transactionSourceDir)) {
                $transactionFiles = glob($transactionSourceDir . '/*.json');
                foreach ($transactionFiles as $file) {
                    $targetFile = $transactionTargetDir . '/' . basename($file);
                    if (!copy($file, $targetFile)) {
                        $success = false;
                        break;
                    }
                }
            }

            // Clean up
            $this->deleteDirectory($extractPath);

            if ($success) {
                $this->message = 'Backup restored successfully!';
                $this->messageType = 'success';
                return true;
            } else {
                $this->message = 'Failed to restore backup.';
                $this->messageType = 'error';
                return false;
            }
        } else {
            $this->message = 'Invalid ZIP file.';
            $this->messageType = 'error';
            return false;
        }
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
