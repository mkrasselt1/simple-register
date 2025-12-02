<?php
/**
 * Home Controller
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../auth.php';

class HomeController extends Controller {
    
    public function index() {
        authenticate();
        $this->render('home');
    }
}
