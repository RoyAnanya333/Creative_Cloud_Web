<?php
include("../config/config.php");

if(!isset($_GET['tutor_id'])){
    echo json_encode(['error'=>'Tutor ID required']);
    exit;
}

$tutor_id = intval($_GET['tutor_id']);

// Fetch tutor info with LEFT JOIN to check if profile exists
$stmt = $conn->prepare("
    SELECT u.id, u.full_name, u.username, u.email, u.contact_number, tp.id AS tutor_profile_id, tp.tutor_code
    FROM users u
    LEFT JOIN tutor_profiles tp ON tp.user_id = u.id
    WHERE u.id=? AND u.user_type='tutor'
");
$stmt->bind_param("i",$tutor_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if(!$res){
    echo json_encode(['error'=>'Tutor not found']);
    exit;
}

// Auto-create tutor profile if missing
if(!$res['tutor_profile_id']){
    $conn->query("INSERT INTO tutor_profiles (user_id) VALUES ($tutor_id)");
    $stmt2 = $conn->prepare("SELECT id, tutor_code FROM tutor_profiles WHERE user_id=?");
    $stmt2->bind_param("i",$tutor_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result()->fetch_assoc();
    $res['tutor_profile_id'] = $res2['id'];
    $res['tutor_code'] = $res2['tutor_code'];
}

echo json_encode($res);
