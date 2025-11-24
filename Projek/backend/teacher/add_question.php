<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_teacher();

$data = json_decode(file_get_contents('php://input'), true);
$quiz_id = (int)($data['quiz_id'] ?? 0);
$text = trim($data['text'] ?? '');
$type = $data['question_type'] ?? 'single';
$points = (int)($data['points'] ?? 1);
$choices = $data['choices'] ?? [];

if ($quiz_id <= 0 || $text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO questions (quiz_id,text,points,question_type) VALUES (?,?,?,?)");
$stmt->bind_param('isis', $quiz_id, $text, $points, $type);
$stmt->execute();
$qid = $stmt->insert_id;
$stmt->close();

if ($type !== 'text' && is_array($choices)) {
    $cStmt = $mysqli->prepare("INSERT INTO choices (question_id,text,is_correct) VALUES (?,?,?)");
    foreach ($choices as $c) {
        $t = $c['text'];
        $correct = $c['is_correct'] ? 1 : 0;
        $cStmt->bind_param('isi', $qid, $t, $correct);
        $cStmt->execute();
    }
    $cStmt->close();
}

echo json_encode(['message' => 'Pertanyaan ditambahkan']);
