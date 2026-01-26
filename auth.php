<?php
/**
 * HTTP Basic Authentication handler
 */

require_once __DIR__ . '/config.php';

function authenticate() {
    if(empty(AUTH_USER) && empty(AUTH_PASS)){
        return;
    }
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        sendAuthChallenge();
    }
    
    if ($_SERVER['PHP_AUTH_USER'] !== AUTH_USER || $_SERVER['PHP_AUTH_PW'] !== AUTH_PASS) {
        sendAuthChallenge();
    }
}

function sendAuthChallenge() {
    header('WWW-Authenticate: Basic realm="Simple Register"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required';
    exit;
}
