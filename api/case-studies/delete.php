<?php

include("../../config/cors.php");
include("../../config/database.php");
include("../../middleware/auth.php");

$id = $_GET['id'] ?? '';

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

if (
    !empty($caseStudy['cover_image']) &&
    file_exists("../../" . $caseStudy['cover_image'])
) {

    unlink("../../" . $caseStudy['cover_image']);
}

mysqli_query(
    $conn,
    "DELETE FROM case_studies WHERE id='$id'"
);

echo json_encode([
    "success" => true,
    "message" => "Case study deleted successfully"
]);