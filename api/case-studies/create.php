<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../config/cors.php");
include("../../config/database.php");
include("../../middleware/auth.php");

try {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $client = trim($_POST['client'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $content = trim($_POST['content'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($title) ||
        empty($category) ||
        empty($client) ||
        empty($year) ||
        empty($content)
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
    | IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    $imagePath = null;

    if (
        isset($_FILES['cover_image']) &&
        $_FILES['cover_image']['error'] == 0
    ) {

        $folder = "../../uploads/case-studies/";

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $extension = pathinfo(
            $_FILES['cover_image']['name'],
            PATHINFO_EXTENSION
        );

        $fileName =
            time() . '-' . uniqid() . '.' . $extension;

        $targetFile = $folder . $fileName;

        move_uploaded_file(
            $_FILES['cover_image']['tmp_name'],
            $targetFile
        );

        $imagePath =
            "uploads/case-studies/" . $fileName;
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT CASE STUDY
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO case_studies (
            title,
            category,
            client,
            year,
            cover_image,
            content
        ) VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $title,
        $category,
        $client,
        $year,
        $imagePath,
        $content
    );

    $insert = mysqli_stmt_execute($stmt);

    if (!$insert) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to create case study"
        ]);

        exit;
    }

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Case study created successfully",
        "id" => mysqli_insert_id($conn)
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}