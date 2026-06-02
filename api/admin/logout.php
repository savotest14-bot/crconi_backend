<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../config/cors.php");

include("../../config/database.php");

include("../../middleware/auth.php");

try {

    /*
    |--------------------------------------------------------------------------
    | GET ADMIN ID
    |--------------------------------------------------------------------------
    */

    $adminId = $authUser['id'];

    /*
    |--------------------------------------------------------------------------
    | REMOVE TOKEN FROM DATABASE
    |--------------------------------------------------------------------------
    */

    $nullToken = null;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE admins
         SET token = ?
         WHERE id = ?"
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
        $nullToken,
        $adminId
    );

    $updateResult = mysqli_stmt_execute(
        $stmt
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
            "message" => "Failed to logout"
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
        "message" => "Logout successful"
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