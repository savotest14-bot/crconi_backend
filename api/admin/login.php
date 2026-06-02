<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../config/cors.php");

require "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Dotenv\Dotenv;

include("../../config/database.php");

try {

    /*
    |--------------------------------------------------------------------------
    | LOAD ENV
    |--------------------------------------------------------------------------
    */

    $dotenv = Dotenv::createImmutable(
        __DIR__ . '/../../'
    );

    $dotenv->load();

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

    $email = trim(
        $data['email'] ?? ''
    );

    $password = trim(
        $data['password'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($email) ||
        empty($password)
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Email and password are required"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | FIND ADMIN
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

    /*
    |--------------------------------------------------------------------------
    | INVALID LOGIN
    |--------------------------------------------------------------------------
    */

    if (
        !$admin ||
        !password_verify(
            $password,
            $admin['password']
        )
    ) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE JWT TOKEN
    |--------------------------------------------------------------------------
    */

    $payload = [

        "id" => $admin['id'],

        "email" => $admin['email'],

        "exp" => time() + (60 * 60 * 24)
    ];

    $token = JWT::encode(
        $payload,
        $_ENV['JWT_SECRET'],
        'HS256'
    );

    /*
    |--------------------------------------------------------------------------
    | SAVE TOKEN IN DATABASE
    |--------------------------------------------------------------------------
    */

    $updateStmt = mysqli_prepare(
        $conn,
        "UPDATE admins
         SET token = ?
         WHERE id = ?"
    );

    if (!$updateStmt) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to prepare token update query"
        ]);

        exit;
    }

    mysqli_stmt_bind_param(
        $updateStmt,
        "ss",
        $token,
        $admin['id']
    );

    $updateResult = mysqli_stmt_execute(
        $updateStmt
    );

    if (!$updateResult) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to save login token"
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

        "message" => "Login successful",

        "token" => $token,

        "data" => [

            "id" => $admin['id'],

            "name" => $admin['name'],

            "email" => $admin['email']
        ]
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