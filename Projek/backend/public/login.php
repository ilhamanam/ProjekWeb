<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$display_name = $data['display_name'] ?? '';
$role = ($data['role'] === 'teacher') ? 'teacher' : 'student';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Username dan password wajib diisi']);
    exit;
} /// bagus kontol

$stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Username sudah terdaftar']);
    exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO users (username,password_hash,display_name,role) VALUES (?,?,?,?)");
$stmt->bind_param('ssss', $username, $hash, $display_name, $role);
$stmt->execute();

echo json_encode(['message' => 'Registrasi berhasil']);

