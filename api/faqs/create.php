<?php

include("../../config/cors.php");
include("../../config/database.php");
include("../../middleware/auth.php");

try {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $page = trim($data['page'] ?? '');
    $question = trim($data['question'] ?? '');
    $answer = trim($data['answer'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($page) ||
        empty($question) ||
        empty($answer)
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "statusCode" => 400,
            "message" => "All fields are required"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT FAQ
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO faqs (
            page,
            question,
            answer
        ) VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $page,
        $question,
        $answer
    );

    $insert = mysqli_stmt_execute($stmt);

    if (!$insert) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "statusCode" => 500,
            "message" => "Failed to create FAQ"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "statusCode" => 201,
        "message" => "FAQ created successfully",
        "id" => mysqli_insert_id($conn)
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "statusCode" => 500,
        "message" => $e->getMessage()
    ]);
}