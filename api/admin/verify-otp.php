<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../config/cors.php");
include("../../config/database.php");

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

    $otp = trim(
        $data['otp'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($email) ||
        empty($otp)
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Email and OTP are required"
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
    | OTP FORMAT CHECK
    |--------------------------------------------------------------------------
    */

    if (!preg_match('/^[0-9]{6}$/', $otp)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "OTP must be 6 digits"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | FIND OTP RECORD
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM password_reset_tokens
         WHERE email = ?
         AND otp = ?
         LIMIT 1"
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
        "ss",
        $email,
        $otp
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $record = mysqli_fetch_assoc($result);

    /*
    |--------------------------------------------------------------------------
    | INVALID OTP
    |--------------------------------------------------------------------------
    */

    if (!$record) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid OTP"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK IF OTP ALREADY USED
    |--------------------------------------------------------------------------
    */

    if ($record['is_verified'] == 1) {

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "OTP already used"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK OTP EXPIRY
    |--------------------------------------------------------------------------
    */

    $currentTime = date("Y-m-d H:i:s");

    if (
        $currentTime >
        $record['otp_expiry']
    ) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "OTP expired"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE RESET TOKEN
    |--------------------------------------------------------------------------
    */

    $resetToken = bin2hex(
        random_bytes(32)
    );

    $tokenExpiry = date(
        "Y-m-d H:i:s",
        strtotime("+10 minutes")
    );

    /*
    |--------------------------------------------------------------------------
    | UPDATE RESET TOKEN
    |--------------------------------------------------------------------------
    */

    $updateStmt = mysqli_prepare(
        $conn,
        "UPDATE password_reset_tokens
         SET
            reset_token = ?,
            token_expiry = ?,
            is_verified = 1
         WHERE id = ?"
    );

    if (!$updateStmt) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to prepare update query"
        ]);

        exit;
    }

    mysqli_stmt_bind_param(
        $updateStmt,
        "ssi",
        $resetToken,
        $tokenExpiry,
        $record['id']
    );

    $updateResult = mysqli_stmt_execute(
        $updateStmt
    );

    if (!$updateResult) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to verify OTP"
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
        "message" => "OTP verified successfully",
        "reset_token" => $resetToken
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