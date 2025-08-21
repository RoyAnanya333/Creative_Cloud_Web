<?php
session_start();
include("../config/config.php");
include("../includes/student_header.php");

// Ensure student is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student'){
    header("Location: ../guest/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
?>

<link rel="stylesheet" href="../assets/css/dashboard.css">

<div class="content">
    <h2>Your Notifications</h2>

    <div class="cards">
        <?php
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                echo "<div class='card'>
                        <strong>".htmlspecialchars($row['title'])."</strong>
                        <p>".htmlspecialchars($row['body'])."</p>
                        <small>".date("d M Y H:i", strtotime($row['created_at']))."</small>
                      </div>";
            }
        } else {
            echo "<p>No notifications yet.</p>";
        }
        ?>
    </div>
</div>

<?php include("../includes/student_footer.php"); ?>
