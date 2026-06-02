<?php

include("../../config/cors.php");
include("../../config/database.php");

try {

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    $page =
        isset($_GET['page'])
        ? (int) $_GET['page']
        : 1;

    $limit =
        isset($_GET['limit'])
        ? (int) $_GET['limit']
        : 10;

    $offset =
        ($page - 1) * $limit;

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    $category =
        trim($_GET['category'] ?? '');

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
    | WHERE CONDITIONS
    |--------------------------------------------------------------------------
    */

    $where = "WHERE 1=1";

    if (!empty($category)) {

        $category =
            mysqli_real_escape_string(
                $conn,
                $category
            );

        $where .=
            " AND category = '$category'";
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL COUNT
    |--------------------------------------------------------------------------
    */

    $countQuery = mysqli_query(
        $conn,
        "SELECT COUNT(*) as total
         FROM blogs
         $where"
    );

    $totalData =
        mysqli_fetch_assoc($countQuery);

    $totalBlogs =
        (int) $totalData['total'];

    /*
    |--------------------------------------------------------------------------
    | GET BLOGS
    |--------------------------------------------------------------------------
    */

    $query = mysqli_query(
        $conn,
        "SELECT *
         FROM blogs
         $where
         ORDER BY created_at DESC
         LIMIT $limit OFFSET $offset"
    );

    if (!$query) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch blogs"
        ]);

        exit;
    }

    $blogs = [];

    while ($row = mysqli_fetch_assoc($query)) {

        /*
        |--------------------------------------------------------------------------
        | TAGS JSON DECODE
        |--------------------------------------------------------------------------
        */

        $row['tags'] =
            json_decode($row['tags'], true);

        /*
        |--------------------------------------------------------------------------
        | IMAGE FULL URL
        |--------------------------------------------------------------------------
        */

        if (!empty($row['cover_image'])) {

            $row['cover_image'] =
                $baseUrl . $row['cover_image'];
        }

        $blogs[] = $row;
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION DATA
    |--------------------------------------------------------------------------
    */

    $totalPages =
        ceil($totalBlogs / $limit);

    /*
    |--------------------------------------------------------------------------
    | SUCCESS RESPONSE
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Blogs fetched successfully",

        "pagination" => [
            "current_page" => $page,
            "limit" => $limit,
            "total_blogs" => $totalBlogs,
            "total_pages" => $totalPages
        ],

        "filters" => [
            "category" => $category
        ],

        "data" => $blogs
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