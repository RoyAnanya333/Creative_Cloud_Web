<?php
include("../includes/header_tutor.php");
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

$ticket_id = intval($_GET['id'] ?? 0);
if(!$ticket_id){
    echo "Invalid ticket ID.";
    exit;
}

// Fetch ticket
$stmt = $conn->prepare("SELECT * FROM tutor_tickets WHERE id=? AND tutor_profile_id=?");
$stmt->bind_param("ii",$ticket_id,$tutor_profile_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
if(!$ticket){
    echo "Ticket not found or access denied.";
    exit;
}

// Handle reply
$errors = [];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $reply = trim($_POST['reply'] ?? '');
    if($reply){
        $stmt = $conn->prepare("INSERT INTO tutor_ticket_replies (ticket_id, sender_type, message) VALUES (?, 'tutor', ?)");
        $stmt->bind_param("is", $ticket_id, $reply);
        if($stmt->execute()){
            header("Location: view_ticket.php?id=$ticket_id");
            exit;
        } else {
            $errors[] = "Database error: ".$conn->error;
        }
    } else {
        $errors[] = "Reply cannot be empty.";
    }
}

// Fetch all replies
$replies = $conn->query("SELECT * FROM tutor_ticket_replies WHERE ticket_id=$ticket_id ORDER BY created_at ASC");
?>

<div class="content">
    <h2>Ticket: <?= htmlspecialchars($ticket['subject']) ?></h2>
    <p><strong>Status:</strong> <?= ucfirst($ticket['status']) ?> | <strong>Priority:</strong> <?= ucfirst($ticket['priority']) ?></p>
    <p><strong>Message:</strong></p>
    <div style="background:#f3f4f6; padding:10px; border-radius:5px;"><?= nl2br(htmlspecialchars($ticket['message'])) ?></div>

    <h3>Replies</h3>
    <?php if($replies->num_rows > 0): ?>
        <?php while($r = $replies->fetch_assoc()): ?>
            <div style="background:<?= $r['sender_type']=='admin'?'#dbeafe':'#dcfce7' ?>; padding:8px; margin-bottom:5px; border-radius:4px;">
                <strong><?= ucfirst($r['sender_type']) ?>:</strong> <?= nl2br(htmlspecialchars($r['message'])) ?>
                <br><small><?= $r['created_at'] ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No replies yet.</p>
    <?php endif; ?>

    <?php if($ticket['status']!=='closed'): ?>
        <?php if($errors): ?>
            <div class="error-box">
                <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
            </div>
        <?php endif; ?>
        <form method="POST" style="margin-top:15px;">
            <label>Reply:</label>
            <textarea name="reply" rows="4" required></textarea>
            <button type="submit" class="btn">Send Reply</button>
        </form>
    <?php else: ?>
        <p><em>This ticket is closed. You cannot reply.</em></p>
    <?php endif; ?>

    <a href="messages.php" class="btn" style="margin-top:10px;">Back to Messages</a>
</div>
