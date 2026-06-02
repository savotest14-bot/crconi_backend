<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../config/cors.php");

include("../../config/database.php");
include("../../helpers/mail.php");

try {

    /*
    |--------------------------------------------------------------------------
    | GET INPUT
    |--------------------------------------------------------------------------
    */

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $email = trim(
        $data['email'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (empty($email)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Email is required"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VALID EMAIL FORMAT
    |--------------------------------------------------------------------------
    */

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Invalid email format"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK ADMIN EXISTS
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM admins WHERE email = ? LIMIT 1"
    );

    if (!$stmt) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Database statement preparation failed"
        ]);

        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $email
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $admin = mysqli_fetch_assoc($result);

    if (!$admin) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Email does not exist"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE OTP
    |--------------------------------------------------------------------------
    */

    $otp = rand(100000, 999999);

    $otpExpiry = date(
        "Y-m-d H:i:s",
        strtotime("+10 minutes")
    );

    /*
    |--------------------------------------------------------------------------
    | DELETE OLD OTP
    |--------------------------------------------------------------------------
    */

    $deleteStmt = mysqli_prepare(
        $conn,
        "DELETE FROM password_reset_tokens WHERE email = ?"
    );

    mysqli_stmt_bind_param(
        $deleteStmt,
        "s",
        $email
    );

    mysqli_stmt_execute($deleteStmt);

    /*
    |--------------------------------------------------------------------------
    | SAVE OTP
    |--------------------------------------------------------------------------
    */

    $insertStmt = mysqli_prepare(
        $conn,
        "INSERT INTO password_reset_tokens (
            email,
            otp,
            otp_expiry
        ) VALUES (?, ?, ?)"
    );

    if (!$insertStmt) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to prepare OTP insert query"
        ]);

        exit;
    }

    mysqli_stmt_bind_param(
        $insertStmt,
        "sss",
        $email,
        $otp,
        $otpExpiry
    );

    $insertResult = mysqli_stmt_execute(
        $insertStmt
    );

    if (!$insertResult) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to save OTP"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | SEND MAIL
    |--------------------------------------------------------------------------
    */

    $subject = "Password Reset OTP";

    $body = "
        <h3>Your Password Reset OTP</h3>

        <p>Your OTP is:</p>

        <h2>$otp</h2>

        <p>Valid for 10 minutes.</p>
    ";

    $mailSent = sendMail(
        $email,
        $subject,
        $body
    );

    if (!$mailSent) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to send OTP email"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | SUCCESS RESPONSE
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "OTP sent successfully"
    ]);

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | SERVER ERROR
    |--------------------------------------------------------------------------
    */

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}