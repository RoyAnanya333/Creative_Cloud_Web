<?php
include("../config/config.php");

$id = $_GET['id'] ?? 0;

if ($id) {
    $stmt = $conn->prepare("UPDATE users SET is_active=0 WHERE id=? AND user_type='student'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: students.php");
exit;
