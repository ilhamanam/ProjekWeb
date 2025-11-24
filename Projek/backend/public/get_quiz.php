<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $mysqli->prepare("SELECT id, title, description FROM quizzes WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
if (!$quiz) {
    http_response_code(404);
    echo json_encode(['error' => 'Quiz tidak ditemukan']);
    exit;
}

$qStmt = $mysqli->prepare("SELECT id, text, points, question_type FROM questions WHERE quiz_id=?");
$qStmt->bind_param('i', $id);
$qStmt->execute();
$qResult = $qStmt->get_result();
$questions = [];
while ($q = $qResult->fetch_assoc()) {
    $choices = [];
    if ($q['question_type'] !== 'text') {
        $cStmt = $mysqli->prepare("SELECT id,text FROM choices WHERE question_id=?");
        $cStmt->bind_param('i', $q['id']);
        $cStmt->execute();
        $choices = $cStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $cStmt->close();
    }
    $q['choices'] = $choices;
    $questions[] = $q;
}
$qStmt->close();

$quiz['questions'] = $questions;
echo json_encode($quiz);
