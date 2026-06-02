<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../config/cors.php");
include("../../config/database.php");
include("../../middleware/auth.php");

try {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $readTime = trim($_POST['read_time'] ?? '');

    $tags = $_POST['tags'] ?? '[]';

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($title) ||
        empty($category) ||
        empty($author) ||
        empty($content)
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Required fields missing"
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

        $folder = "../../uploads/blogs/";

        // Create folder if not exists
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

        // Upload image
        if (
            move_uploaded_file(
                $_FILES['cover_image']['tmp_name'],
                $targetFile
            )
        ) {

            $imagePath =
                "uploads/blogs/" . $fileName;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT BLOG
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO blogs (
            title,
            category,
            author,
            cover_image,
            content,
            tags,
            read_time
        ) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssssss",
        $title,
        $category,
        $author,
        $imagePath,
        $content,
        $tags,
        $readTime
    );

    $insert = mysqli_stmt_execute($stmt);

    if (!$insert) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => mysqli_error($conn)
        ]);

        exit;
    }

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Blog created successfully"
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}