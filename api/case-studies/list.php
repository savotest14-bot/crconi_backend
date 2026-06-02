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

// $baseUrl =
//     $protocol . $host . "/backend/";

$baseUrl =
    $protocol . $host . "/";

/*
|--------------------------------------------------------------------------
| GET QUERY PARAMS
|--------------------------------------------------------------------------
*/

$page = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

$limit = isset($_GET['limit'])
    ? (int) $_GET['limit']
    : 10;

$offset = ($page - 1) * $limit;

$category = trim($_GET['category'] ?? '');
$client = trim($_GET['client'] ?? '');
$year = trim($_GET['year'] ?? '');

/*
|--------------------------------------------------------------------------
| FILTER CONDITIONS
|--------------------------------------------------------------------------
*/

$whereConditions = [];
$params = [];
$types = '';

if (!empty($category)) {
    $whereConditions[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($client)) {
    $whereConditions[] = "client = ?";
    $params[] = $client;
    $types .= 's';
}

if (!empty($year)) {
    $whereConditions[] = "year = ?";
    $params[] = $year;
    $types .= 's';
}

$whereSql = '';

if (!empty($whereConditions)) {
    $whereSql =
        "WHERE " . implode(' AND ', $whereConditions);
}

/*
|--------------------------------------------------------------------------
| TOTAL COUNT QUERY
|--------------------------------------------------------------------------
*/

$countQuery = "
    SELECT COUNT(*) as total
    FROM case_studies
    $whereSql
";

$countStmt = mysqli_prepare($conn, $countQuery);

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
    FROM case_studies
    $whereSql
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($conn, $query);

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

$data = [];

while ($row = mysqli_fetch_assoc($result)) {

    /*
    |--------------------------------------------------------------------------
    | FULL IMAGE URL
    |--------------------------------------------------------------------------
    */

    if (!empty($row['cover_image'])) {
        $row['cover_image'] =
            $baseUrl . $row['cover_image'];
    }

    $data[] = $row;
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
    "currentPage" => $page,
    "limit" => $limit,
    "total" => $total,
    "totalPages" => ceil($total / $limit),
    "filters" => [
        "category" => $category,
        "client" => $client,
        "year" => $year
    ],
    "data" => $data
]);