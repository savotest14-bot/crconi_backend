<?php

/*
|--------------------------------------------------------------------------
| AUTO ADMIN SEEDER
|--------------------------------------------------------------------------
*/

$adminName = $_ENV['ADMIN_NAME'];

$adminEmail = $_ENV['ADMIN_EMAIL'];

$adminPassword = $_ENV['ADMIN_PASSWORD'];

/*
|--------------------------------------------------------------------------
| DEFAULT TOKEN
|--------------------------------------------------------------------------
*/

$adminToken = null;

/*
|--------------------------------------------------------------------------
| CHECK IF ANY ADMIN EXISTS
|--------------------------------------------------------------------------
*/

$checkAdminQuery = "
    SELECT id
    FROM admins
    LIMIT 1
";

$checkAdminResult = mysqli_query(
    $conn,
    $checkAdminQuery
);

if (mysqli_num_rows($checkAdminResult) == 0) {

    /*
    |--------------------------------------------------------------------------
    | HASH PASSWORD
    |--------------------------------------------------------------------------
    */

    $hashedPassword = password_hash(
        $adminPassword,
        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | INSERT ADMIN
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO admins (
            name,
            email,
            password,
            token
        ) VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $adminName,
        $adminEmail,
        $hashedPassword,
        $adminToken
    );

    mysqli_stmt_execute($stmt);
}