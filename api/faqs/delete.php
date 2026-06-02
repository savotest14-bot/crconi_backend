<?php

include("../../config/cors.php");
include("../../config/database.php");
include("../../middleware/auth.php");

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
        "message" => "FAQ ID required"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| CHECK FAQ EXISTS
|--------------------------------------------------------------------------
*/

$checkStmt = mysqli_prepare(
    $conn,
    "SELECT id FROM faqs WHERE id = ?"
);

mysqli_stmt_bind_param(
    $checkStmt,
    "i",
    $id
);

mysqli_stmt_execute($checkStmt);

$checkResult =
    mysqli_stmt_get_result($checkStmt);

$faq =
    mysqli_fetch_assoc($checkResult);

if (!$faq) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "statusCode" => 404,
        "message" => "FAQ not found"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE FAQ
|--------------------------------------------------------------------------
*/

$deleteStmt = mysqli_prepare(
    $conn,
    "DELETE FROM faqs WHERE id = ?"
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
        "statusCode" => 500,
        "message" => "Failed to delete FAQ"
    ]);

    exit;
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
    "message" => "FAQ deleted successfully"
]);