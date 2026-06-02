<?php

include("../../config/cors.php");
include("../../config/database.php");
include("../../middleware/auth.php");

try {

    $id = $_POST['id'] ?? '';

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

    if (empty($id)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Blog ID required"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | GET BLOG
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM blogs WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $blog = mysqli_fetch_assoc($result);

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
    | EXISTING IMAGE
    |--------------------------------------------------------------------------
    */

    $imagePath = $blog['cover_image'];

    /*
    |--------------------------------------------------------------------------
    | NEW IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['cover_image']) &&
        $_FILES['cover_image']['error'] == 0
    ) {

        /*
        |--------------------------------------------------------------------------
        | DELETE OLD IMAGE
        |--------------------------------------------------------------------------
        */

        if (
            !empty($blog['cover_image']) &&
            file_exists("../../" . $blog['cover_image'])
        ) {

            unlink("../../" . $blog['cover_image']);
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE FOLDER
        |--------------------------------------------------------------------------
        */

        $folder = "../../uploads/blogs/";

        if (!file_exists($folder)) {

            mkdir($folder, 0777, true);
        }

        /*
        |--------------------------------------------------------------------------
        | UPLOAD NEW IMAGE
        |--------------------------------------------------------------------------
        */

        $extension = pathinfo(
            $_FILES['cover_image']['name'],
            PATHINFO_EXTENSION
        );

        $fileName =
            time() . '-' . uniqid() . '.' . $extension;

        $targetFile = $folder . $fileName;

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
    | UPDATE BLOG
    |--------------------------------------------------------------------------
    */

    $updateStmt = mysqli_prepare(
        $conn,
        "UPDATE blogs
         SET
            title = ?,
            category = ?,
            author = ?,
            cover_image = ?,
            content = ?,
            tags = ?,
            read_time = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $updateStmt,
        "sssssssi",
        $title,
        $category,
        $author,
        $imagePath,
        $content,
        $tags,
        $readTime,
        $id
    );

    $update = mysqli_stmt_execute($updateStmt);

    if (!$update) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => mysqli_error($conn)
        ]);

        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Blog updated successfully"
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}