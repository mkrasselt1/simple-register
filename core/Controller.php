<?php

/**
 * Base Controller Class
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Language.php';

abstract class Controller {
    /**
     * Render a view with data
     * @param string $view View template name
     * @param array $data Data to pass to the view
     */
    protected function render($view, $data = []) {
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $data['csrf_token'] = $_SESSION['csrf_token'];
        View::display($view, $data);
    }

    /**
     * Redirect to another URL
     * @param string $url URL to redirect to
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    /**
     * Send JSON response
     * @param mixed $data Data to send
     * @param int $statusCode HTTP status code
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get POST data
     * @param string $key Key to get
     * @param mixed $default Default value
     * @return mixed
     */
    protected function post($key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    /**
     * Validate CSRF token
     * @return bool
     */
    protected function validateCsrf() {
        $token = $this->post('csrf_token');
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Regenerate CSRF token
     */
    protected function regenerateCsrf() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    /**
     * Get GET data
     * @param string $key Key to get
     * @param mixed $default Default value
     * @return mixed
     */
    protected function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if request is POST
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Get JSON input
     * @return array|null Returns null if JSON is invalid
     */
    protected function getJsonInput() {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input;
    }

    /**
     * Get language instance
     */
    protected function lang() {
        return Language::getInstance();
    }

    /**
     * Set language
     */
    protected function setLanguage($lang) {
        $this->lang()->setLanguage($lang);
    }

    /**
     * Get translated string
     */
    protected function t($key, $default = null) {
        return $this->lang()->get($key, $default);
    }
}
