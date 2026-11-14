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
            $this->message = $this->t('invalid_request', 'Invalid request');
            $this->messageType = 'error';
            return;
        }
        // Valid, now handle
        $action = $this->post('action', '');
        // Per-row delete button inside the bulk edit form
        if ($this->post('delete_id', '') !== '') {
            $action = 'delete';
        }

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
            case 'bulk_update':
                if ($this->bulkUpdateArticles()) {
                    $this->regenerateCsrf();
                }
                break;
            case 'bulk_delete':
                if ($this->bulkDeleteArticles()) {
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

        if ($name && $this->isPriceInRange($price)) {
            addArticle($name, $price, $color);
            $this->message = $this->t('article_added', 'Article added successfully!');
            $this->messageType = 'success';
        } else {
            $this->message = $this->t('invalid_article_data', 'Please provide a valid name and price.');
            $this->messageType = 'error';
        }
    }

    private function updateArticle()
    {
        $id = $this->post('id', '');
        $name = trim($this->post('name', ''));
        $price = floatval($this->post('price', 0));
        $color = $this->post('color', '#007bff');

        if ($id && $name && $this->isPriceInRange($price)) {
            if (updateArticle($id, $name, $price, $color)) {
                $this->message = $this->t('article_updated', 'Article updated successfully!');
                $this->messageType = 'success';
            } else {
                $this->message = $this->t('article_not_found', 'Article not found.');
                $this->messageType = 'error';
            }
        } else {
            $this->message = $this->t('invalid_article_data', 'Please provide a valid name and price.');
            $this->messageType = 'error';
        }
    }

    private function deleteArticle()
    {
        $id = $this->post('delete_id', '') ?: $this->post('id', '');

        if ($id && deleteArticle($id)) {
            $this->message = $this->t('article_deleted', 'Article deleted successfully!');
            $this->messageType = 'success';
            return true;
        }
        $this->message = $this->t('article_not_found', 'Article not found.');
        $this->messageType = 'error';
        return false;
    }

    /**
     * Update several articles at once.
     * Expects $_POST['articles'][<id>] = ['name' => ..., 'price' => ..., 'color' => ...]
     */
    private function bulkUpdateArticles()
    {
        $input = $this->post('articles', []);
        if (!is_array($input) || empty($input)) {
            $this->message = $this->t('bulk_nothing_to_save', 'No articles submitted.');
            $this->messageType = 'error';
            return false;
        }

        $articles = getArticles();
        $updated = 0;
        $invalid = [];

        foreach ($input as $id => $data) {
            if (!is_array($data) || !isset($articles[$id])) {
                continue;
            }
            $name = trim((string) ($data['name'] ?? ''));
            $priceRaw = str_replace(',', '.', trim((string) ($data['price'] ?? '')));
            $color = (string) ($data['color'] ?? $articles[$id]->color);

            if ($name === '' || !is_numeric($priceRaw) || !$this->isPriceInRange((float) $priceRaw)
                || !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                $invalid[] = $name !== '' ? $name : $articles[$id]->name;
                continue;
            }
            $price = round((float) $priceRaw, 2);

            $article = $articles[$id];
            if ($article->name !== $name || (float) $article->price !== $price || $article->color !== $color) {
                $article->name = $name;
                $article->price = $price;
                $article->color = $color;
                $updated++;
            }
        }

        if ($updated > 0) {
            saveArticles($articles);
        }

        if ($invalid) {
            $this->message = sprintf($this->t('bulk_updated_with_errors', '%d article(s) saved. Invalid data, not saved: %s'),
                $updated, implode(', ', $invalid));
            $this->messageType = 'error';
        } else {
            $this->message = sprintf($this->t('bulk_updated', '%d article(s) saved.'), $updated);
            $this->messageType = 'success';
        }
        return $updated > 0;
    }

    /**
     * Delete all selected articles. Expects $_POST['selected'] = [<id>, ...]
     */
    private function bulkDeleteArticles()
    {
        $ids = $this->post('selected', []);
        if (!is_array($ids) || empty($ids)) {
            $this->message = $this->t('bulk_none_selected', 'No articles selected.');
            $this->messageType = 'error';
            return false;
        }

        $articles = getArticles();
        $deleted = 0;
        foreach ($ids as $id) {
            if (is_string($id) && isset($articles[$id])) {
                unset($articles[$id]);
                $deleted++;
            }
        }
        if ($deleted > 0) {
            saveArticles($articles);
        }
        $this->message = sprintf($this->t('bulk_deleted', '%d article(s) deleted.'), $deleted);
        $this->messageType = $deleted > 0 ? 'success' : 'error';
        return $deleted > 0;
    }

    private function addUser()
    {
        $username = trim($this->post('username', ''));
        $normalizedUsername = strtolower($username);
        $password = $this->post('password', '');
        $confirmPassword = $this->post('confirm_password', '');

        if ($normalizedUsername && $password) {
            if ($password !== $confirmPassword) {
                $this->message = $this->t('passwords_do_not_match', 'Passwords do not match.');
                $this->messageType = 'error';
                return;
            }
            $users = $this->getUsers();
            // Check if user exists
            foreach ($users as $user) {
                if (strtolower(trim($user['username'])) === $normalizedUsername) {
                    $this->message = $this->t('user_exists', 'User already exists.');
                    $this->messageType = 'error';
                    return;
                }
            }
            $users[] = [
                'username' => $normalizedUsername,
                'password' => $password,
                'permissions' => ['user'] // default
            ];
            file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
            $this->message = $this->t('user_added', 'User added successfully!');
            $this->messageType = 'success';
        } else {
            $this->message = $this->t('provide_username_password', 'Please provide username and password.');
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

    private function isPriceInRange($price)
    {
        return $price >= PRICE_MIN && $price <= PRICE_MAX;
    }

    private function downloadBackup()
    {
        if (!class_exists('ZipArchive')) {
            $this->message = $this->t('zip_not_available', 'ZIP extension is not available. Please contact your administrator.');
            $this->messageType = 'error';
            return;
        }

        $dataDir = __DIR__ . '/../data';
        $zip = new ZipArchive();
        $zipFile = tempnam(sys_get_temp_dir(), 'backup') . '.zip';

        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            $this->addDirectoryToZip($zip, $dataDir, 'data');
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="simple-register-backup-' . date('Y-m-d-H-i-s') . '.zip"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);
            unlink($zipFile);
            exit;
        } else {
            $this->message = $this->t('backup_failed', 'Failed to create backup.');
            $this->messageType = 'error';
        }
    }

    private function uploadRestore()
    {
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            $this->message = $this->t('upload_error', 'No file uploaded or upload error.');
            $this->messageType = 'error';
            return false;
        }

        if (!class_exists('ZipArchive')) {
            $this->message = $this->t('zip_not_available', 'ZIP extension is not available. Please contact your administrator.');
            $this->messageType = 'error';
            return false;
        }

        $file = $_FILES['backup_file'];
        $allowedTypes = ['application/zip', 'application/x-zip-compressed'];

        if (!in_array($file['type'], $allowedTypes) && !preg_match('/\.zip$/i', $file['name'])) {
            $this->message = $this->t('only_zip_allowed', 'Only ZIP files are allowed.');
            $this->messageType = 'error';
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) === true) {
            $extractPath = __DIR__ . '/../temp_restore_' . time();
            mkdir($extractPath, 0755, true);

            $zip->extractTo($extractPath);
            $zip->close();

            // Restore the entire data directory
            $dataSourceDir = $extractPath . '/data';
            $dataTargetDir = __DIR__ . '/../data';

            if (is_dir($dataSourceDir)) {
                // First, remove existing data directory contents (except .htaccess maybe?)
                $this->deleteDirectoryContents($dataTargetDir);

                // Copy all files from backup
                $this->copyDirectory($dataSourceDir, $dataTargetDir);
                $success = true;
            } else {
                $success = false;
            }

            // Clean up
            $this->deleteDirectory($extractPath);

            if ($success) {
                $this->message = $this->t('backup_restored', 'Backup restored successfully!');
                $this->messageType = 'success';
                return true;
            } else {
                $this->message = $this->t('restore_failed', 'Failed to restore backup.');
                $this->messageType = 'error';
                return false;
            }
        } else {
            $this->message = $this->t('invalid_zip', 'Invalid ZIP file.');
            $this->messageType = 'error';
            return false;
        }
    }

    private function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private function deleteDirectoryContents($dir)
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
    }

    private function addDirectoryToZip($zip, $dir, $zipPath)
    {
        $this->addFilesToZipRecursive($zip, realpath($dir), $zipPath);
    }

    private function addFilesToZipRecursive($zip, $dir, $zipPath)
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $fullPath = $dir . '/' . $file;
            $relativePath = $zipPath . '/' . $file;

            if (is_dir($fullPath)) {
                $this->addFilesToZipRecursive($zip, $fullPath, $relativePath);
            } else {
                $zip->addFile($fullPath, $relativePath);
            }
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
