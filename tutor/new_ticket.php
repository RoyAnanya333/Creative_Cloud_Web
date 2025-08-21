<?php
include("../includes/header_tutor.php");  // Tutor header + session
include("../config/config.php");

$tutor_user_id = $_SESSION['user_id'] ?? 0;
if(!$tutor_user_id){
    header("Location: ../login.php");
    exit;
}

// Get tutor_profile_id
$stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=?");
$stmt->bind_param("i",$tutor_user_id);
$stmt->execute();
$tutor_profile = $stmt->get_result()->fetch_assoc();
$tutor_profile_id = $tutor_profile['id'] ?? 0;

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'normal';

    if(!$subject) $errors[] = "Subject is required.";
    if(!$message) $errors[] = "Message is required.";

    if(empty($errors)){
        $stmt = $conn->prepare("INSERT INTO tutor_tickets (tutor_profile_id, subject, message, priority) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $tutor_profile_id, $subject, $message, $priority);
        if($stmt->execute()){
            header("Location: messages.php?success=1");
            exit;
        } else {
            $errors[] = "Database error: ".$conn->error;
        }
    }
}
?>

<div class="content">
    <h1>New Ticket</h1>

    <?php if($errors): ?>
        <div class="error-box">
            <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="max-width:600px;">
        <label>Subject</label>
        <input type="text" name="subject" required>

        <label>Message</label>
        <textarea name="message" rows="5" required></textarea>

        <label>Priority</label>
        <select name="priority">
            <option value="low">Low</option>
            <option value="normal" selected>Normal</option>
            <option value="high">High</option>
        </select>

        <button type="submit" class="btn">Submit Ticket</button>
        <a href="messages.php" class="btn">Back</a>
    </form>
</div>
