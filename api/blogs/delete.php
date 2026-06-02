<?php

include("../../config/cors.php");
include("../../config/database.php");
// include("../../middleware/auth.php");

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
    | DELETE IMAGE FROM SERVER
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
    | DELETE BLOG
    |--------------------------------------------------------------------------
    */

    $deleteStmt = mysqli_prepare(
        $conn,
        "DELETE FROM blogs WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $deleteStmt,
        "i",
        $id
    );

    $delete =
        mysqli_stmt_execute($deleteStmt);

    if (!$delete) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to delete blog"
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
        "message" => "Blog deleted successfully"
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