<?php

include("../../config/cors.php");
include("../../config/database.php");

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
| GET ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? '';

if (empty($id)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "statusCode" => 400,
        "message" => "ID is required"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| GET CASE STUDY
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM case_studies
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$caseStudy = mysqli_fetch_assoc($result);

/*
|--------------------------------------------------------------------------
| NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$caseStudy) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "statusCode" => 404,
        "message" => "Case study not found"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| FULL IMAGE URL
|--------------------------------------------------------------------------
*/

if (!empty($caseStudy['cover_image'])) {

    $caseStudy['cover_image'] =
        $baseUrl . $caseStudy['cover_image'];
}

/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

http_response_code(200);

echo json_encode([
    "success" => true,
    "statusCode" => 200,
    "baseUrl" => $baseUrl,
    "data" => $caseStudy
]);