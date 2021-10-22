<?php

require_once 'app/start.php';

$errors = [];

if (isset($_POST) && !empty($_POST)) {

    if (isset($_POST['fpr'])) {
        $changes = [];
        if (isset($_POST['name']) && !empty($_POST['name']) && is_string($_POST['name']) && !empty(normal_text($_POST['name']))) {
            $user_name = normal_text($_POST['name']);
    
            if ($user_name !== $logged_user['user_name']) {
                $changes['user_name'] = $user_name;
            }
        } else {
            $errors[] = "Name cannot be empty";
        }
    
        if (isset($_POST['email']) && !empty($_POST['email']) && is_string($_POST['email']) && !empty(normal_text($_POST['email']))) {
            $user_email = normal_text($_POST['email']);
            
            if ($user_email !== $logged_user['user_email']) {
                if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Email format is incorrect";
                } else {
                    // checking if email already in db
                    $existing = $U->get_user_by('user_email', $user_email);
                    if ($existing['status']) {
                        $errors[] = "Email already registered, try different.";
                    } else {
                        $changes['user_email'] = $user_email;
                    }
                }
            }
        } else {
            $errors[] = "Email cannot be empty";
        }
    
        if (empty($errors)) {
            if (!empty($changes)) {
                $check = $U->update($changes, $logged_user['user_id']);
                if ($check['status']) {
                    $user = $check['user'];
                    
                    $_SESSION['message'] = ['type' => 'success', 'data' => 'Profile is successfully updated.'];
                    move('settings.php');
                } else {
                    $errors[] = "Unable to update profile.";
                }
            } else {
                $_SESSION['message'] = ['type' => 'success', 'data' => 'No data is changed.'];
                move('settings.php');
            }
        }
    }


    if (isset($_POST['fpa'])) {

        if (isset($_POST['password']) && !empty($_POST['password']) && is_string($_POST['password']) && !empty(normal_text($_POST['password']))) {
            $user_password = normal_text($_POST['password']);
    
            if (isset($_POST['repassword']) && !empty($_POST['repassword']) && is_string($_POST['repassword']) && !empty(normal_text($_POST['repassword']))) {
                $user_repassword = normal_text($_POST['repassword']);
    
                if ($user_password !== $user_repassword) {
                    $errors[] = "Passwords doesn't match, type again";
                }
            } else {
                $errors[] = "Retype your password";
            }
        } else {
            $errors[] = "Password cannot be empty";
        }

        if (empty($errors)) {

            $user_password_hashed = password_hash($user_password, PASSWORD_BCRYPT); 
            $changes = ['user_password' => $user_password_hashed];
            
            $check = $U->update($changes, $logged_user['user_id']);
            if ($check['status']) {
                $_SESSION['message'] = ['type' => 'success', 'data' => 'Password is successfully updated.'];
                move('settings.php');
            } else {
                $errors[] = "Unable to update profile.";
            }

        }

    }

}

require_once DIR.'views/layout/header.view.php';
require_once DIR.'views/pages/settings.view.php';
require_once DIR.'views/layout/footer.view.php';
