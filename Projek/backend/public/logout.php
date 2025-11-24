<?php
header('Content-Type: application/json');
session_start();
session_destroy();
echo json_encode(['message' => 'Logout ber']);
// bagus ini kenapa gamuncul yang udah lu ubah?
?>
