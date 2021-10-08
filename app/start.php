<?php

    session_start();

    // Main project directory
    define('DIR', dirname(__DIR__).'/');
    
    // Either: development/production
    define('PROJECT_MODE', 'development'); 

    if (PROJECT_MODE !== 'development') {
        error_reporting(0);
    } else {
        error_reporting(E_ALL);
    }

    // Database details
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'songs_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    // Timezone setting
    define('TIMEZONE', 'Europe/Berlin');
    date_default_timezone_set(TIMEZONE);

    // Auto load classes
    require DIR . 'app/auto_loader.php';

    // Functions
    require DIR . 'app/functions.php';

    // Get db handle
    $db = (new DB())->connect();
    $settings = new Settings($db);

    define('URL', $settings->url());
    
    // Mailer settings
        // server settings
    define('IS_SMTP', ($settings->fetch('mail_smtp') === '1' ? true : false));
    define('SMTP_HOST', $settings->fetch('mail_smtp_host'));
    define('SMTP_AUTH', ($settings->fetch('mail_smtp_auth') === '1' ? true : false)); // smtp server requires authentication? true or false
    define('SMTP_USERNAME', $settings->fetch('mail_smtp_username'));
    define('SMTP_PASSWORD', $settings->fetch('mail_smtp_password'));
    define('SMTP_ENCRYPTION', $settings->fetch('mail_smtp_encryption')); // either tls or smtps
    define('SMTP_PORT', $settings->fetch('mail_smtp_port')); // default is 465
        // mail settings
    define('MAIL_FROM', $settings->fetch('mail_from_email'));
    define('MAIL_FROM_NAME', $settings->fetch('mail_from_name'));

    $U = new Users($db);

    // checking for session message
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        if ($_SESSION['message']['type'] === 'success') {
            $success = $_SESSION['message']['data'];
        } else if ($_SESSION['message']['type'] === 'error') {
            $error = $_SESSION['message']['data'];
        }
        unset($_SESSION['message']);
    }

    $logged = false;
    if (isset($_SESSION['logged']) && !empty($_SESSION['logged']) && $_SESSION['logged'] === true) {
        $logged = true;
    }
