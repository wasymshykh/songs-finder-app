<?php 

require_once 'app/start.php';

if (!isset($logged) || !$logged) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Authentication required to access page'];
    move('login.php');
}

