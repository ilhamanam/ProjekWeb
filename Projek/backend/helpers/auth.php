<?php

session_start();

function is_logged_in() {
    return isset($_SESSION['user']);
}
function current_user() {
    return $_SESSION['user'] ?? null;
}
function require_login() {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['error' => 'Belum login']);
        exit;
    }
}
function require_teacher() {
    if (!is_logged_in() || $_SESSION['user']['role'] !== 'teacher') {
        http_response_code(403);
        echo json_encode(['error' => 'Hanya teacher yang boleh']);
        exit;
    }
}
