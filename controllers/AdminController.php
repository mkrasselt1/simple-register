<?php
/**
 * Admin Controller - Manages Articles
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../articles.php';

class AdminController extends Controller {
    
    private $message = '';
    private $messageType = '';
    
    public function index() {
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
    
    private function handlePost() {
        $action = $this->post('action', '');
        
        switch ($action) {
            case 'add':
                $this->addArticle();
                break;
            case 'update':
                $this->updateArticle();
                break;
            case 'delete':
                $this->deleteArticle();
                break;
            case 'add_user':
                $this->addUser();
                break;
        }
    }
    
    private function addArticle() {
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
    
    private function updateArticle() {
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
    
    private function deleteArticle() {
        $id = $this->post('id', '');
        
        if ($id && deleteArticle($id)) {
            $this->message = 'Article deleted successfully!';
            $this->messageType = 'success';
        } else {
            $this->message = 'Article not found.';
            $this->messageType = 'error';
        }
    }
    
    private function addUser() {
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
    
    private function getUsers() {
        $usersFile = USERS_FILE;
        if (!file_exists($usersFile)) {
            return [];
        }
        $data = json_decode(file_get_contents($usersFile), true);
        return $data ?: [];
    }
}
