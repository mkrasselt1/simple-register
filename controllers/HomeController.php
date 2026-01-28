<?php
/**
 * Home Controller
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/AuthController.php';

class HomeController extends Controller {
    
    public function index() {
        $auth = new AuthController();
        $auth->checkAuth();
        $this->render('home');
    }
}
