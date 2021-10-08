<?php 

require_once 'app/start.php';

if (isset($_POST) && !empty($_POST)) {

    if (isset($_POST['email']) && !empty($_POST['email']) && is_string($_POST['email']) && !empty(normal_text($_POST['email']))) {
        $user_email = normal_text($_POST['email']);
        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email format is incorrect";
        }
    } else {
        $errors[] = "Email cannot be empty";
    }

    if (isset($_POST['password']) && !empty($_POST['password']) && is_string($_POST['password']) && !empty(normal_text($_POST['password']))) {
        $user_password = normal_text($_POST['password']);
    } else {
        $errors[] = "Password cannot be empty";
    }

    if (empty($errors)) {

        $check = $U->login($user_email, $user_password);
        if ($check['status']) {
            $user = $check['user'];
            if ($user['user_role'] === 'U') {
                move('index.php');
            }
        } else {
            $errors[] = $check['data'];
        }
        
    }

}

require_once DIR.'views/layout/header.view.php';
require_once DIR.'views/pages/login.view.php';
require_once DIR.'views/layout/footer.view.php';
