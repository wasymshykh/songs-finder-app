<?php 

use Ramsey\Uuid\Uuid;

require_once 'app/start.php';

$errors = [];

if (isset($_POST) && !empty($_POST)) {
    
    if (isset($_POST['name']) && !empty($_POST['name']) && is_string($_POST['name']) && !empty(normal_text($_POST['name']))) {
        $user_name = normal_text($_POST['name']);
    } else {
        $errors[] = "Name cannot be empty";
    }

    if (isset($_POST['email']) && !empty($_POST['email']) && is_string($_POST['email']) && !empty(normal_text($_POST['email']))) {
        $user_email = normal_text($_POST['email']);
        
        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email format is incorrect";
        } else {

            // checking if email already in db
            $existing = $U->get_user_by('user_email', $user_email);
            if ($existing['status']) {
                $errors[] = "Email already registered, try different.";
            }

        }
    } else {
        $errors[] = "Email cannot be empty";
    }

    if (isset($_POST['password']) && !empty($_POST['password']) && is_string($_POST['password']) && !empty(normal_text($_POST['password']))) {
        $user_password = normal_text($_POST['password']);

        if (isset($_POST['repassword']) && !empty($_POST['repassword']) && is_string($_POST['repassword']) && !empty(normal_text($_POST['repassword']))) {
            
            $user_repassword = normal_text($_POST['repassword']);

            if ($user_password !== $user_password) {
                $errors[] = "Passwords doesn't match, type again";
            }

        } else {
            $errors[] = "Retype your password";
        }
    } else {
        $errors[] = "Password cannot be empty";
    }


    if (empty($errors)) {

        $user_verification = $settings->fetch('user_email_verification');
        $user_password_hashed = password_hash($user_password, PASSWORD_BCRYPT);
        
        if ($user_verification === '0') {
            // Email verification is set to false -> direct registration
            $check = $U->register($user_name, $user_email, $user_password_hashed, 'U', 'A');
            if ($check['status']) {
                $_SESSION['message'] = ['type' => 'success', 'data' => "Account created. You can now access your account."];
                move('login.php');
            } else {
                $errors[] = "Unable to create your account. Try again.";
            }
        } else {
            
            $mail = new MailHelper();
            $verification_code = (Uuid::uuid4())->toString();

            try {
                $db->beginTransaction();

                $check = $U->register($user_name, $user_email, $user_password_hashed,'U', 'U');

                if ($check['status']) {
                    
                    $user_id = $check['user_id'];
                    
                    $C = new Codes($db);
                    $check = $C->record($user_id, $verification_code, 'V');
                    
                    if ($check['status']) {

                        $link = URL."/verify.php?c=".$verification_code."&u=".$user_id;
                        
                        $check = $mail->send_reset_email($user_email, "Verify your account to access ".$settings->fetch('site_name'), $link);
                        if ($check['status']) {
                            
                            $db->commit();
                            
                            $_SESSION['message'] = ['type' => 'success', 'data' => 'Thank you for registration, check inbox for activation mail.'];
                            move('register.php?s=done');
    
                        } else {
                            $db->rollBack();
                            $errors[] = "E.0.402: Unable to register account";
                        }

                    } else {
                        $db->rollBack();
                        $errors[] = "E.0.401: Unable to register account";
                    }
                } else {
                    $db->rollBack();
                    $errors[] = "E.0.400: Unable to register account";
                }                
                
            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = "E.0.399: Unable to register account";
            }

        }

    }

}


require_once DIR.'views/layout/header.view.php';
require_once DIR.'views/pages/register.view.php';
require_once DIR.'views/layout/footer.view.php';
