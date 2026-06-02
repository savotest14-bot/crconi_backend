<?php

include("../../config/cors.php");
include("../../config/database.php");
include("../../middleware/auth.php");

try {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $id = $data['id'] ?? '';

    $page = trim($data['page'] ?? '');
    $question = trim($data['question'] ?? '');
    $answer = trim($data['answer'] ?? '');

    if (empty($id)) {

        echo json_encode([
            "success" => false,
            "message" => "FAQ ID required"
        ]);

        exit;
    }

    $query = mysqli_query(
        $conn,
        "SELECT * FROM faqs WHERE id='$id'"
    );

    $faq = mysqli_fetch_assoc($query);

    if (!$faq) {

        echo json_encode([
            "success" => false,
            "message" => "FAQ not found"
        ]);

        exit;
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE faqs
         SET
            page = ?,
            question = ?,
            answer = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $page,
        $question,
        $answer,
        $id
    );

    mysqli_stmt_execute($stmt);

    echo json_encode([
        "success" => true,
        "message" => "FAQ updated successfully"
    ]);

} catch (Throwable $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}