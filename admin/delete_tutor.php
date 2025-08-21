<?php
include("../includes/header_admin.php");
include("../config/config.php");

if(!isset($_GET['id'])){
    header("Location: tutors.php");
    exit;
}

$tutor_id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM users WHERE id=? AND user_type='tutor'");
$stmt->bind_param("i", $tutor_id);
$stmt->execute();

$_SESSION['success'] = "Tutor removed successfully.";
header("Location: tutors.php");
exit;
?>
