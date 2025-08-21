<?php
// classwork.php
header('Content-Type: application/json');
include __DIR__ . '/../config/config.php';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    // Tutor creating classwork
    if ($action === 'create') {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
            echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
        }
        $title = trim($_POST['title'] ?? '');
        $course_id = intval($_POST['course_id'] ?? 0);
        $work_type = $_POST['work_type'] ?? 'assignment';
        $due_at = $_POST['due_at'] ?? null;
        $max_points = intval($_POST['max_points'] ?? 0);

        $tutor_profile_id = null;
        $stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($tpid);
        if ($stmt->fetch()) $tutor_profile_id = $tpid;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO classwork (course_id, tutor_profile_id, title, description, work_type, due_at, max_points, created_at) VALUES (?,?,?,?,?,?,?,NOW())");
        $desc = $_POST['description'] ?? null;
        $stmt->bind_param("iisssis", $course_id, $tutor_profile_id, $title, $desc, $work_type, $due_at, $max_points);
        $ok = $stmt->execute();
        echo json_encode(['success'=>$ok, 'id'=>$stmt->insert_id]);
        exit;
    }

    // Student submitting classwork
    if ($action === 'submit') {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
            echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
        }
        $classwork_id = intval($_POST['classwork_id'] ?? 0);
        $text_answer = $_POST['text_answer'] ?? null;
        $file_url = $_POST['file_url'] ?? null; // if using upload, you'd handle file saving

        $stmt = $conn->prepare("INSERT INTO submissions (classwork_id, student_id, submitted_at, file_url, text_answer, status) VALUES (?,?,?,?,?, 'submitted')");
        $sid = $_SESSION['user_id'];
        $stmt->bind_param("iisss", $classwork_id, $sid, date('Y-m-d H:i:s'), $file_url, $text_answer);
        $ok = $stmt->execute();
        echo json_encode(['success'=>$ok, 'submission_id'=>$stmt->insert_id]);
        exit;
    }
}

if ($method === 'GET') {
    $course_id = intval($_GET['course_id'] ?? 0);
    if ($course_id <= 0) { echo json_encode(['success'=>false,'error'=>'Invalid course']); exit; }
    $stmt = $conn->prepare("SELECT * FROM classwork WHERE course_id=? ORDER BY created_at DESC");
    $stmt->bind_param("i",$course_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'classwork'=>$rows]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Unsupported method']);
