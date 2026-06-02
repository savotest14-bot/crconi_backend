<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../config/cors.php");

include("../../config/database.php");

include("../../middleware/auth.php");

try {

    /*
    |--------------------------------------------------------------------------
    | GET JSON BODY
    |--------------------------------------------------------------------------
    */

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    /*
    |--------------------------------------------------------------------------
    | GET INPUTS
    |--------------------------------------------------------------------------
    */

    $oldPassword = trim(
        $data['old_password'] ?? ''
    );

    $newPassword = trim(
        $data['new_password'] ?? ''
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
        empty($oldPassword) ||
        empty($newPassword) ||
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

    if (strlen($newPassword) < 6) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "New password must be at least 6 characters"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD MATCH CHECK
    |--------------------------------------------------------------------------
    */

    if ($newPassword !== $confirmPassword) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "New password and confirm password do not match"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK SAME PASSWORD
    |--------------------------------------------------------------------------
    */

    if ($oldPassword === $newPassword) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "New password cannot be same as old password"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | GET USER FROM TOKEN
    |--------------------------------------------------------------------------
    */

    $adminId = $authUser['id'];

    /*
    |--------------------------------------------------------------------------
    | FETCH ADMIN
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM admins
         WHERE id = ?
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
        $adminId
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $admin = mysqli_fetch_assoc($result);

    /*
    |--------------------------------------------------------------------------
    | ADMIN NOT FOUND
    |--------------------------------------------------------------------------
    */

    if (!$admin) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Admin not found"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFY OLD PASSWORD
    |--------------------------------------------------------------------------
    */

    if (
        !password_verify(
            $oldPassword,
            $admin['password']
        )
    ) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Old password is incorrect"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | HASH NEW PASSWORD
    |--------------------------------------------------------------------------
    */

    $hashedPassword = password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */

    $updateStmt = mysqli_prepare(
        $conn,
        "UPDATE admins
         SET password = ?
         WHERE id = ?"
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
        $adminId
    );

    $updateResult = mysqli_stmt_execute(
        $updateStmt
    );

    /*
    |--------------------------------------------------------------------------
    | UPDATE FAILED
    |--------------------------------------------------------------------------
    */

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
    | SUCCESS RESPONSE
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Password changed successfully"
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