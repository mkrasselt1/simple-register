<?php
/**
 * Register Controller - Cash Register Interface
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../articles.php';

class RegisterController extends Controller {
    
    public function index() {
        $auth = new AuthController();
        $auth->checkAuth();
        
        $articles = getArticles();
        $articlesArray = array_map(function($article) {
            return $article->toArray();
        }, $articles);
        $articlesJson = json_encode(array_values($articlesArray));
        
        $this->render('register', [
            'articles' => $articlesArray,
            'articlesJson' => $articlesJson
        ]);
    }
}
