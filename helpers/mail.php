<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

function sendMail(
    $to,
    $subject,
    $body
) {

    /*
    |--------------------------------------------------------------------------
    | LOAD ENV
    |--------------------------------------------------------------------------
    */

    $dotenv = Dotenv::createImmutable(
        __DIR__ . '/../'
    );

    $dotenv->load();

    /*
    |--------------------------------------------------------------------------
    | CREATE MAIL INSTANCE
    |--------------------------------------------------------------------------
    */

    $mail = new PHPMailer(true);

    try {

        /*
        |--------------------------------------------------------------------------
        | SMTP CONFIG
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();

        $mail->Host = $_ENV['MAIL_HOST'];

        $mail->SMTPAuth = true;

        $mail->Username = $_ENV['MAIL_USERNAME'];

        $mail->Password = $_ENV['MAIL_PASSWORD'];

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = $_ENV['MAIL_PORT'];

        /*
        |--------------------------------------------------------------------------
        | FROM
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            $_ENV['MAIL_FROM'],
            $_ENV['MAIL_FROM_NAME']
        );

        /*
        |--------------------------------------------------------------------------
        | TO
        |--------------------------------------------------------------------------
        */

        $mail->addAddress($to);

        /*
        |--------------------------------------------------------------------------
        | EMAIL CONTENT
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $body;

        /*
        |--------------------------------------------------------------------------
        | SEND MAIL
        |--------------------------------------------------------------------------
        */

        return $mail->send();

    } catch (Exception $e) {

        echo json_encode([
            "success" => false,
            "message" => "Mail sending failed",
            "error" => $mail->ErrorInfo
        ]);

        exit;
    }
}
