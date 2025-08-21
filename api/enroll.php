<?php
// enroll.php
header('Content-Type: application/json');
include __DIR__ . '/../config/config.php';

// require login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$student_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$course_id = intval($input['course_id'] ?? 0);
$method = $conn->real_escape_string($input['method'] ?? 'Bkash');

if ($course_id <= 0) {
    echo json_encode(['success'=>false,'error'=>'Invalid course']);
    exit;
}

// fetch course price
$stmt = $conn->prepare("SELECT price FROM courses WHERE id = ?");
$stmt->bind_param("i",$course_id);
$stmt->execute();
$stmt->bind_result($price);
if (!$stmt->fetch()) {
    echo json_encode(['success'=>false,'error'=>'Course not found']);
    exit;
}
$stmt->close();

// Check if already enrolled
$stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
$stmt->bind_param("ii", $student_id, $course_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success'=>false,'error'=>'Already enrolled']);
    exit;
}
$stmt->close();

// Create payment (simulate paid)
$status = 'paid';
$txn_ref = 'TXN'.time().rand(100,999);
$stmt = $conn->prepare("INSERT INTO payments (student_id, course_id, amount, currency, method, txn_ref, status, paid_at) VALUES (?,?,?,?,?,?,?,NOW())");
$currency = 'BDT';
$stmt->bind_param("iiissss", $student_id, $course_id, $price, $currency, $method, $txn_ref, $status);
$ok = $stmt->execute();
$payment_id = $stmt->insert_id;
$stmt->close();

if (!$ok) {
    echo json_encode(['success'=>false,'error'=>'Payment failed']);
    exit;
}

// Create enrollment
$stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, status, payment_id, enrollment_date) VALUES (?,?, 'active', ?, NOW())");
$stmt->bind_param("iii", $student_id, $course_id, $payment_id);
$ok2 = $stmt->execute();
$enrollment_id = $stmt->insert_id;
$stmt->close();

if (!$ok2) {
    // revert payment if needed
    echo json_encode(['success'=>false,'error'=>'Enrollment failed']);
    exit;
}

echo json_encode(['success'=>true, 'payment_id'=>$payment_id, 'enrollment_id'=>$enrollment_id]);
