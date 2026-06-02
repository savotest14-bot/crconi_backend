<?php

include("../../config/cors.php");
include("../../config/database.php");

/*
|--------------------------------------------------------------------------
| GET QUERY PARAMS
|--------------------------------------------------------------------------
*/

$pageFilter = trim($_GET['page'] ?? '');

$page = isset($_GET['pageNumber'])
    ? (int) $_GET['pageNumber']
    : 1;

$limit = isset($_GET['limit'])
    ? (int) $_GET['limit']
    : 10;

$offset = ($page - 1) * $limit;

/*
|--------------------------------------------------------------------------
| FILTER CONDITIONS
|--------------------------------------------------------------------------
*/

$whereSql = '';
$params = [];
$types = '';

if (!empty($pageFilter)) {

    $whereSql = "WHERE page = ?";
    $params[] = $pageFilter;
    $types .= 's';
}

/*
|--------------------------------------------------------------------------
| TOTAL COUNT QUERY
|--------------------------------------------------------------------------
*/

$countQuery = "
    SELECT COUNT(*) as total
    FROM faqs
    $whereSql
";

$countStmt = mysqli_prepare(
    $conn,
    $countQuery
);

if (!empty($params)) {

    mysqli_stmt_bind_param(
        $countStmt,
        $types,
        ...$params
    );
}

mysqli_stmt_execute($countStmt);

$countResult =
    mysqli_stmt_get_result($countStmt);

$totalRow =
    mysqli_fetch_assoc($countResult);

$total = (int) $totalRow['total'];

/*
|--------------------------------------------------------------------------
| MAIN QUERY
|--------------------------------------------------------------------------
*/

$query = "
    SELECT *
    FROM faqs
    $whereSql
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare(
    $conn,
    $query
);

/*
|--------------------------------------------------------------------------
| BIND PARAMETERS
|--------------------------------------------------------------------------
*/

$params[] = $limit;
$params[] = $offset;

$types .= 'ii';

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$faqs = [];

while ($row = mysqli_fetch_assoc($result)) {
    $faqs[] = $row;
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
    "currentPage" => $page,
    "limit" => $limit,
    "total" => $total,
    "totalPages" => ceil($total / $limit),
    "filters" => [
        "page" => $pageFilter
    ],
    "data" => $faqs
]);