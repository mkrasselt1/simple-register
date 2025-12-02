<?php
/**
 * Admin Controller - Manages Articles
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../articles.php';

class AdminController extends Controller {
    
    private $message = '';
    private $messageType = '';
    
    public function index() {
        authenticate();
        
        // Handle form submissions
        if ($this->isPost()) {
            $this->handlePost();
        }
        
        $articles = getArticles();
        
        $this->render('admin', [
            'articles' => $articles,
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
}
