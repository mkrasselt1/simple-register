<?php
/**
 * Register Controller - Cash Register Interface
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../articles.php';

class RegisterController extends Controller {
    
    public function index() {
        authenticate();
        
        $articles = getArticles();
        $articlesJson = json_encode(array_values($articles));
        
        $this->render('register', [
            'articles' => $articles,
            'articlesJson' => $articlesJson
        ]);
    }
}
