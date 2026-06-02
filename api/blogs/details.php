<?php

include("../../config/cors.php");
include("../../config/database.php");

try {

    /*
    |--------------------------------------------------------------------------
    | GET BLOG ID
    |--------------------------------------------------------------------------
    */

    $id = $_GET['id'] ?? '';

    if (empty($id)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Blog ID is required"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | DYNAMIC BASE URL
    |--------------------------------------------------------------------------
    */

    $protocol =
        (!empty($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off')
        ? "https://"
        : "http://";

    $host = $_SERVER['HTTP_HOST'];

    $baseUrl =
        $protocol . $host . "/backend/";

    /*
    |--------------------------------------------------------------------------
    | GET BLOG
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM blogs
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    $blog =
        mysqli_fetch_assoc($result);

    /*
    |--------------------------------------------------------------------------
    | BLOG NOT FOUND
    |--------------------------------------------------------------------------
    */

    if (!$blog) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Blog not found"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | TAGS JSON DECODE
    |--------------------------------------------------------------------------
    */

    $blog['tags'] =
        json_decode($blog['tags'], true);

    /*
    |--------------------------------------------------------------------------
    | IMAGE FULL URL
    |--------------------------------------------------------------------------
    */

    if (!empty($blog['cover_image'])) {

        $blog['cover_image'] =
            $baseUrl . $blog['cover_image'];
    }

    /*
    |--------------------------------------------------------------------------
    | SUCCESS RESPONSE
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Blog fetched successfully",
        "data" => $blog
    ]);

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | ERROR RESPONSE
    |--------------------------------------------------------------------------
    */

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}