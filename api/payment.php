<?php
// payment.php
header('Content-Type: application/json');
include __DIR__ . '/../config/config.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$course_id = intval($input['course_id'] ?? 0);

if ($course_id <= 0) {
    echo json_encode(['success'=>false,'error'=>'Invalid course']);
    exit;
}

$stmt = $conn->prepare("SELECT id, title, price, currency FROM courses WHERE id=?");
$stmt->bind_param("i",$course_id);
$stmt->execute();
$res = $stmt->get_result();
$course = $res->fetch_assoc();
if (!$course) {
    echo json_encode(['success'=>false,'error'=>'Course not found']);
    exit;
}

$session_id = 'PSN'.time().rand(1000,9999); // fake session id
echo json_encode([
    'success'=>true,
    'session' => [
        'session_id'=>$session_id,
        'course_id'=>$course['id'],
        'title'=>$course['title'],
        'amount'=>$course['price'],
        'currency'=>$course['currency'] ?? 'BDT'
    ]
]);
