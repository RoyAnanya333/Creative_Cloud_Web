<?php
include("../includes/header_admin.php");
include("../config/config.php");

if(!isset($_GET['id'])){
    header("Location: tutors.php");
    exit;
}

$tutor_id = intval($_GET['id']);

// Check if tutor exists
$stmt = $conn->prepare("SELECT id, is_active FROM users WHERE id=? AND user_type='tutor' LIMIT 1");
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    $_SESSION['error'] = "Tutor not found.";
    header("Location: tutors.php");
    exit;
}

// Update to active
$stmt = $conn->prepare("UPDATE users SET is_active=1 WHERE id=?");
$stmt->bind_param("i", $tutor_id);
$stmt->execute();

$_SESSION['success'] = "Tutor unrestricted successfully.";
header("Location: tutors.php");
exit;
?>
