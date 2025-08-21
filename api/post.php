<?php
// post.php
header('Content-Type: application/json');
include __DIR__ . '/../config/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
        echo json_encode(['success'=>false,'error'=>'Unauthorized']);
        exit;
    }
    $data = $_POST;
    $course_id = intval($data['course_id'] ?? 0);
    $content = trim($data['content'] ?? '');
    if ($course_id<=0 || $content === '') {
        echo json_encode(['success'=>false,'error'=>'Missing data']);
        exit;
    }
    $tutor_profile_id = null;
    // map user_id to tutor_profiles.id
    $stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($tpid);
    if ($stmt->fetch()) $tutor_profile_id = $tpid;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO posts (course_id, tutor_profile_id, author_user_id, content, created_at) VALUES (?,?,?,?,NOW())");
    $stmt->bind_param("iiis", $course_id, $tutor_profile_id, $_SESSION['user_id'], $content);
    $ok = $stmt->execute();
    $id = $stmt->insert_id;
    echo json_encode(['success'=>$ok, 'post_id'=>$id]);
    exit;
}

if ($method === 'GET') {
    $course_id = intval($_GET['course_id'] ?? 0);
    if ($course_id <= 0) { echo json_encode(['success'=>false,'error'=>'Invalid course']); exit; }

    $stmt = $conn->prepare("SELECT p.id, p.content, p.created_at, u.username AS author FROM posts p JOIN users u ON p.author_user_id = u.id WHERE p.course_id=? ORDER BY p.created_at DESC");
    $stmt->bind_param("i",$course_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'posts'=>$rows]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Unsupported method']);
