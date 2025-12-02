<?php
/**
 * Base Controller Class
 */

require_once __DIR__ . '/View.php';

abstract class Controller {
    /**
     * Render a view with data
     * @param string $view View template name
     * @param array $data Data to pass to the view
     */
    protected function render($view, $data = []) {
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
     * @return array
     */
    protected function getJsonInput() {
        return json_decode(file_get_contents('php://input'), true) ?: [];
    }
}
