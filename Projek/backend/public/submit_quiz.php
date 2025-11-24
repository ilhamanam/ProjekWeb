<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_login();

$data = json_decode(file_get_contents('php://input'), true);
$quiz_id = (int)($data['quiz_id'] ?? 0);
$answers = $data['answers'] ?? [];

$user_id = current_user()['id'];

$stmt = $mysqli->prepare("INSERT INTO submissions (quiz_id,user_id,raw_json) VALUES (?,?,?)");
$json_raw = json_encode($answers);
$stmt->bind_param('iis', $quiz_id, $user_id, $json_raw);
$stmt->execute();
$submission_id = $stmt->insert_id;
$stmt->close();

$total_score = 0;
foreach ($answers as $ans) {
    $q_id = (int)$ans['question_id'];
    $answer_text = $ans['answer_text'] ?? '';
    $is_correct = 0;
    $points = 0;

    $qStmt = $mysqli->prepare("SELECT question_type, points FROM questions WHERE id=?");
    $qStmt->bind_param('i', $q_id);
    $qStmt->execute();
    $qData = $qStmt->get_result()->fetch_assoc();
    $qStmt->close();

    if ($qData['question_type'] !== 'text') {
        $selected = $ans['selected'] ?? [];
        if (!is_array($selected)) $selected = [$selected];
        $ids = implode(',', array_map('intval', $selected));
        if ($ids) {
            $cResult = $mysqli->query("SELECT COUNT(*) as benar FROM choices WHERE id IN ($ids) AND is_correct=1");
            $benar = $cResult->fetch_assoc()['benar'];
            $totalBenar = $mysqli->query("SELECT COUNT(*) as tot FROM choices WHERE question_id=$q_id AND is_correct=1")->fetch_assoc()['tot'];
            if ($benar == $totalBenar) {
                $is_correct = 1;
                $points = $qData['points'];
            }
        }
    }

    $total_score += $points;

    $s = $mysqli->prepare("INSERT INTO submission_answers (submission_id,question_id,answer_text,is_correct,points_awarded) VALUES (?,?,?,?,?)");
    $s->bind_param('iisid', $submission_id, $q_id, $answer_text, $is_correct, $points);
    $s->execute();
    $s->close();
}

$mysqli->query("UPDATE submissions SET score=$total_score, finished_at=NOW() WHERE id=$submission_id");
echo json_encode(['message' => 'Jawaban disimpan', 'score' => $total_score]);
