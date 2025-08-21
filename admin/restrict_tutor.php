<?php
include("../includes/header_admin.php");
include("../config/config.php");

if(!isset($_GET['id'])){
    header("Location: tutors.php");
    exit;
}

$tutor_id = intval($_GET['id']);

// Get current status
$stmt = $conn->prepare("SELECT is_active FROM users WHERE id=? AND user_type='tutor' LIMIT 1");
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    $_SESSION['error'] = "Tutor not found.";
    header("Location: tutors.php");
    exit;
}

$row = $result->fetch_assoc();
$new_status = $row['is_active'] ? 0 : 1;

$stmt = $conn->prepare("UPDATE users SET is_active=? WHERE id=?");
$stmt->bind_param("ii", $new_status, $tutor_id);
$stmt->execute();

$_SESSION['success'] = $new_status ? "Tutor unrestricted successfully." : "Tutor restricted successfully.";
header("Location: tutors.php");
exit;
?>
