<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

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
| DATABASE
|--------------------------------------------------------------------------
*/

include(__DIR__ . '/../config/database.php');

/*
|--------------------------------------------------------------------------
| GET AUTH HEADER
|--------------------------------------------------------------------------
*/

$headers = getallheaders();

$authHeader =
    $headers['Authorization']
    ?? $headers['authorization']
    ?? null;

/*
|--------------------------------------------------------------------------
| TOKEN REQUIRED
|--------------------------------------------------------------------------
*/

if (!$authHeader) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Authorization token required"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| VALIDATE BEARER FORMAT
|--------------------------------------------------------------------------
*/

if (
    !preg_match(
        '/Bearer\s(\S+)/',
        $authHeader,
        $matches
    )
) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid authorization format"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| EXTRACT TOKEN
|--------------------------------------------------------------------------
*/

$token = $matches[1];

/*
|--------------------------------------------------------------------------
| VERIFY TOKEN
|--------------------------------------------------------------------------
*/

try {

    $decoded = JWT::decode(
        $token,
        new Key(
            $_ENV['JWT_SECRET'],
            'HS256'
        )
    );

    /*
    |--------------------------------------------------------------------------
    | CHECK TOKEN EXISTS IN DATABASE
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, token
         FROM admins
         WHERE id = ?
         AND token = ?
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
        $decoded->id,
        $token
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $admin = mysqli_fetch_assoc($result);

    /*
    |--------------------------------------------------------------------------
    | INVALID TOKEN
    |--------------------------------------------------------------------------
    */

    if (!$admin) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid or logged out token"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | AUTH USER
    |--------------------------------------------------------------------------
    */

    $authUser = [

        "id" => $admin['id'],

        "name" => $admin['name'],

        "email" => $admin['email']
    ];

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | TOKEN ERROR
    |--------------------------------------------------------------------------
    */

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token"
    ]);

    exit;
}