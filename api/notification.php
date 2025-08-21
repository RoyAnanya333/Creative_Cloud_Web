<?php
// notification.php
header('Content-Type: application/json');
include __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$action = $_REQUEST['action'] ?? 'list';
$uid = $_SESSION['user_id'];

if ($action === 'list') {
    $stmt = $conn->prepare("SELECT id, notif_type, title, body, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->bind_param("i",$uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'notifications'=>$rows]);
    exit;
}

if ($action === 'mark_read') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['success'=>false,'error'=>'Invalid id']); exit; }
    $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
    $stmt->bind_param("ii",$id, $uid);
    $stmt->execute();
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Unknown action']);
