<?php

include("../config/config.php");

$ticket_id = intval($_GET['id']);
$message = '';
$admin_reply = '';
$status = '';

// Fetch the ticket
$stmt = $conn->prepare("
    SELECT tt.id, tt.message, tt.status, u.full_name
    FROM tutor_tickets tt
    JOIN tutor_profiles tp ON tt.tutor_profile_id = tp.id
    JOIN users u ON tp.user_id = u.id
    WHERE tt.id = ?
");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if(!$ticket){
    die("Ticket not found.");
}

// Handle reply submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $admin_reply = $_POST['admin_reply'] ?? '';
    $status = $_POST['status'] ?? $ticket['status'];

    if($admin_reply){
        // Optionally, save reply as a new table row or update ticket message
        $stmt = $conn->prepare("UPDATE tutor_tickets SET message = CONCAT(message, '\n\nAdmin: ', ?), status=? WHERE id=?");
        $stmt->bind_param("ssi", $admin_reply, $status, $ticket_id);
        $stmt->execute();
        echo "<script>alert('Reply sent!'); window.close();</script>";
        exit;
    }
}
?>

<div class="content" style="padding:15px;">
    <h2>Reply to Ticket #<?= $ticket['id'] ?></h2>
    <p><strong>Tutor:</strong> <?= htmlspecialchars($ticket['full_name']) ?></p>
    <p><strong>Original Message:</strong><br><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>

    <form method="POST">
        <label>Reply:</label>
        <textarea name="admin_reply" rows="5" required></textarea>

        <label>Status:</label>
        <select name="status">
            <option value="open" <?= $ticket['status']=='open'?'selected':'' ?>>Open</option>
            <option value="resolved" <?= $ticket['status']=='resolved'?'selected':'' ?>>Resolved</option>
            <option value="closed" <?= $ticket['status']=='closed'?'selected':'' ?>>Closed</option>
        </select>

        <button type="submit" class="btn">Send Reply</button>
    </form>
</div>

<style>
textarea { width:100%; padding:8px; margin-top:4px; margin-bottom:10px; }
select { width:100%; padding:8px; margin-top:4px; margin-bottom:10px; }
button.btn { padding:8px 15px; background:#1e3a8a; color:#fff; border:none; border-radius:4px; cursor:pointer; }
button.btn:hover { background:#3b82f6; }
</style>
