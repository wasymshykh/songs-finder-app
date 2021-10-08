<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper {

    private PHPMailer $mail;
    private $class_name;
    private $class_name_lower;

    public function __construct() {
        $this->logs = new Logs((new DB())->connect());

        $this->class_name = "MailHelper";
        $this->class_name_lower = "mailhelper_class";

        $this->mail = new PHPMailer(true);
        // setting mail server config
        if (IS_SMTP === true) {
            $this->mail->isSMTP();
            $this->mail->Host = SMTP_HOST;
            $this->mail->SMTPAuth = SMTP_AUTH;
            $this->mail->Username = SMTP_USERNAME;
            $this->mail->Password = SMTP_PASSWORD;
            // if (PROJECT_MODE !== 'development') {
                $this->mail->SMTPSecure = SMTP_ENCRYPTION == 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS; // tls or smtps
            // }
            $this->mail->Port = SMTP_PORT;
        }
        $this->mail->isHTML(true);
    }
    
    public function send_reset_email ($to, $subject, $link)
    {
        $this->mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $this->mail->addAddress($to);
        
        $this->mail->Subject = $subject;
        $this->mail->Body = $this->get_verify_mail_body($link);

        return $this->send_mail();
    }

    public function get_verify_mail_body ($link)
    {
        $content = '<p>Hello, <br>You have successfully registered!</p>';
        $content .= '<br><p>Verify your account: <b><a href="'.$link.'">'.$link.'</a></b></p>';
        $content .= '<br><p>Thanks!</p>';
        
        return $content;
    }

    public function send_mail ()
    {
        try {
            $this->mail->send();         
            return ['status' => true];
        } catch (Exception $e) {
            $failure = $this->class_name.'.send_mail - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($e->getMessage()));
            return ['status' => false, 'type' => 'exception', 'data' => $failure];
        }
    }

}
