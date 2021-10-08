<?php

require_once 'app/start.php';

if (isset($_GET['c']) && !empty($_GET['c']) && is_string($_GET['c']) && isset($_GET['u']) && !empty($_GET['u']) && is_numeric($_GET['u'])) {
    
    $code = normal_text($_GET['c']);
    $user_id = normal_text($_GET['u']);

    $C = new Codes($db);

    $check = $C->get_code_by_user_id($code, $user_id, 'V');

    if ($check['status']) {

        // checking if code is already used
        $code = $check['data'];
        if ($code['code_used'] === 'N') {

            // updating user and code
            try {
                $db->beginTransaction();

                $check = $C->mark_code_used($code['code_id']);
                if ($check['status']) {

                    $check = $U->mark_verified($code['code_user_id']);
                    if ($check['status']) {
                        $db->commit();
                        $_SESSION['message'] = ['type' => 'success', 'data' => 'Account is verified, you now access your account.'];
                    } else {
                        $db->rollBack();
                        $_SESSION['message'] = ['type' => 'error', 'data' => 'Unable to verify, try again.'];
                    }

                } else {
                    $db->rollBack();
                    $_SESSION['message'] = ['type' => 'error', 'data' => 'Unable to verify, try again.'];
                }
                
            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['message'] = ['type' => 'error', 'data' => 'E.0.40: Unable to verify, try again.'];
            }

        } else {
            $_SESSION['message'] = ['type' => 'error', 'data' => 'Verification code already used.'];
        }

    } else {
        $_SESSION['message'] = ['type' => 'error', 'data' => 'Verification code is invalid.'];
    }

} else {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Invalid link parameters.'];
}

move('login.php');
