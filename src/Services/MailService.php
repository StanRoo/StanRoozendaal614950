<?php

namespace App\Services;

require_once __DIR__ . '/../../vendor/phpmailer/PHPMailer-6.10.0/src/Exception.php';
require_once __DIR__ . '/../../vendor/phpmailer/PHPMailer-6.10.0/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/PHPMailer-6.10.0/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    public static function send($to, $name, $subject, $body) {
        $config = require __DIR__ . '/../config/mail.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp_user'];
            $mail->Password   = $config['smtp_pass'];
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = $config['smtp_port'];

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ];

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to, $name);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            return ['error' => true, 'message' => "Mailer Error: {$mail->ErrorInfo}"];
        }
    }
}