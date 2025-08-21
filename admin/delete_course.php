<?php
include("../includes/header_admin.php");
include("../config/config.php");

$course_id = $_GET['id'] ?? null;

if(!$course_id){
    die("Course ID is required.");
}

// Fetch course info (to delete banner file)
$stmt = $conn->prepare("SELECT image_url FROM courses WHERE id=?");
$stmt->bind_param("i",$course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
if(!$course){
    die("Course not found.");
}

// Delete course
$stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
$stmt->bind_param("i",$course_id);
if($stmt->execute()){
    // Optionally, delete the banner file
    if($course['image_url'] && file_exists("../".$course['image_url'])){
        unlink("../".$course['image_url']);
    }
    header("Location: courses.php?msg=Course deleted successfully");
    exit;
} else {
    die("Error deleting course: ".$conn->error);
}
?>
