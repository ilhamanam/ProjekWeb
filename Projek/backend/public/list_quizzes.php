<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$result = $mysqli->query("SELECT id, title, description FROM quizzes ORDER BY id DESC");
$quizzes = [];
while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}
echo json_encode($quizzes);
