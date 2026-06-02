<?php

/*
|--------------------------------------------------------------------------
| CREATE ADMINS TABLE
|--------------------------------------------------------------------------
*/

$createAdminsTable = "
    CREATE TABLE IF NOT EXISTS admins (

        id INT AUTO_INCREMENT PRIMARY KEY,

        name VARCHAR(100) NOT NULL,

        email VARCHAR(100) NOT NULL UNIQUE,

        password VARCHAR(255) NOT NULL,

        token TEXT NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    )
";

mysqli_query(
    $conn,
    $createAdminsTable
);

/*
|--------------------------------------------------------------------------
| PASSWORD RESET TOKENS TABLE
|--------------------------------------------------------------------------
*/

$createPasswordResetTable = "
    CREATE TABLE IF NOT EXISTS password_reset_tokens (

        id INT AUTO_INCREMENT PRIMARY KEY,

        email VARCHAR(255) NOT NULL,

        otp VARCHAR(10) NOT NULL,

        otp_expiry DATETIME NOT NULL,

        reset_token VARCHAR(255) DEFAULT NULL,

        token_expiry DATETIME DEFAULT NULL,

        is_verified BOOLEAN DEFAULT 0,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    )
";

mysqli_query(
    $conn,
    $createPasswordResetTable
);


/*
|--------------------------------------------------------------------------
| CREATE BLOGS TABLE
|--------------------------------------------------------------------------
*/

$createBlogsTable = "
    CREATE TABLE IF NOT EXISTS blogs (

       id BIGINT AUTO_INCREMENT PRIMARY KEY,

        title VARCHAR(255) NOT NULL,

        category VARCHAR(100) NOT NULL,

        author VARCHAR(150) NOT NULL,

        cover_image TEXT NULL,

        content LONGTEXT NOT NULL,

        tags JSON NULL,

        read_time VARCHAR(50) NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

    )
";

mysqli_query(
    $conn,
    $createBlogsTable
);

/*
|--------------------------------------------------------------------------
| CREATE CASE STUDIES TABLE
|--------------------------------------------------------------------------
*/

$createCaseStudiesTable = "
    CREATE TABLE IF NOT EXISTS case_studies (

         id BIGINT AUTO_INCREMENT PRIMARY KEY,

        title VARCHAR(255) NOT NULL,

        category VARCHAR(150) NOT NULL,

        client VARCHAR(255) NOT NULL,

        year VARCHAR(20) NOT NULL,

        cover_image TEXT NULL,

        content LONGTEXT NOT NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

    )
";

mysqli_query(
    $conn,
    $createCaseStudiesTable
);

/*
|--------------------------------------------------------------------------
| CREATE FAQS TABLE
|--------------------------------------------------------------------------
*/

$createFaqsTable = "
    CREATE TABLE IF NOT EXISTS faqs (

        id BIGINT AUTO_INCREMENT PRIMARY KEY,

        page VARCHAR(100) NOT NULL,

        question TEXT NOT NULL,

        answer LONGTEXT NOT NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

    )
";

mysqli_query(
    $conn,
    $createFaqsTable
);