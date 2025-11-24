<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_teacher();

$data = json_decode(file_get_contents('php://input'), true);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$created_by = current_user()['id'];

if ($title === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Judul wajib diisi']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO quizzes (title,description,created_by) VALUES (?,?,?)");
$stmt->bind_param('ssi', $title, $description, $created_by);
$stmt->execute();
echo json_encode(['message' => 'Quiz dibuat', 'quiz_id' => $stmt->insert_id]);
