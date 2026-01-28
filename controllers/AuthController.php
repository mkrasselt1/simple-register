<?php
/**
 * Auth Controller - Handles authentication
 */

require_once __DIR__ . '/../core/Controller.php';

class AuthController extends Controller {

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->post('username');
            $password = $this->post('password');

            $users = $this->getUsers();
            foreach ($users as $user) {
                if ($user['username'] === $username && $user['password'] === $password) {
                    $_SESSION['user'] = [
                        'username' => $user['username'],
                        'permissions' => $user['permissions']
                    ];
                    $this->redirect('register.php');
                }
            }
            $this->render('login', ['error' => 'Invalid credentials']);
        } else {
            $this->render('login');
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('login.php');
    }

    public function checkAuth() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login.php');
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