<?php

include("../../config/cors.php");
include("../../config/database.php");
include("../../middleware/auth.php");

try {

    $id = $_POST['id'] ?? '';

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $client = trim($_POST['client'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($id)) {

        echo json_encode([
            "success" => false,
            "message" => "Case study ID required"
        ]);

        exit;
    }

    $query = mysqli_query(
        $conn,
        "SELECT * FROM case_studies WHERE id='$id'"
    );

    $caseStudy = mysqli_fetch_assoc($query);

    if (!$caseStudy) {

        echo json_encode([
            "success" => false,
            "message" => "Case study not found"
        ]);

        exit;
    }

    $imagePath = $caseStudy['cover_image'];

    /*
    |--------------------------------------------------------------------------
    | NEW IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['cover_image']) &&
        $_FILES['cover_image']['error'] == 0
    ) {

        if (
            !empty($caseStudy['cover_image']) &&
            file_exists("../../" . $caseStudy['cover_image'])
        ) {

            unlink("../../" . $caseStudy['cover_image']);
        }

        $folder = "../../uploads/case-studies/";

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

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE case_studies
         SET
            title = ?,
            category = ?,
            client = ?,
            year = ?,
            cover_image = ?,
            content = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssi",
        $title,
        $category,
        $client,
        $year,
        $imagePath,
        $content,
        $id
    );

    mysqli_stmt_execute($stmt);

    echo json_encode([
        "success" => true,
        "message" => "Case study updated successfully"
    ]);

} catch (Throwable $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}