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

    $token = trim(
        $data['token'] ?? ''
    );

    $password = trim(
        $data['password'] ?? ''
    );

    $confirmPassword = trim(
        $data['confirm_password'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($token) ||
        empty($password) ||
        empty($confirmPassword)
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "All fields are required"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD LENGTH CHECK
    |--------------------------------------------------------------------------
    */

    if (strlen($password) < 6) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Password must be at least 6 characters"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD MATCH CHECK
    |--------------------------------------------------------------------------
    */

    if ($password !== $confirmPassword) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Passwords do not match"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | FIND TOKEN
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM password_reset_tokens
         WHERE reset_token = ?
         AND is_verified = 1
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
        "s",
        $token
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $record = mysqli_fetch_assoc($result);

    if (!$record) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid or unverified token"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK TOKEN EXPIRY
    |--------------------------------------------------------------------------
    */

    $currentTime = date("Y-m-d H:i:s");

    if (
        $currentTime >
        $record['token_expiry']
    ) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Reset token expired"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | HASH PASSWORD
    |--------------------------------------------------------------------------
    */

    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | UPDATE ADMIN PASSWORD
    |--------------------------------------------------------------------------
    */

    $updateStmt = mysqli_prepare(
        $conn,
        "UPDATE admins
         SET password = ?
         WHERE email = ?"
    );

    if (!$updateStmt) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to prepare password update query"
        ]);

        exit;
    }

    mysqli_stmt_bind_param(
        $updateStmt,
        "ss",
        $hashedPassword,
        $record['email']
    );

    $updateResult = mysqli_stmt_execute(
        $updateStmt
    );

    if (!$updateResult) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to update password"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE RESET RECORD
    |--------------------------------------------------------------------------
    */

    $deleteStmt = mysqli_prepare(
        $conn,
        "DELETE FROM password_reset_tokens
         WHERE email = ?"
    );

    mysqli_stmt_bind_param(
        $deleteStmt,
        "s",
        $record['email']
    );

    mysqli_stmt_execute($deleteStmt);

    /*
    |--------------------------------------------------------------------------
    | SUCCESS RESPONSE
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Password reset successfully"
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