<?php
include("../includes/header_tutor.php");  // Tutor header + session
include("../config/config.php");

// Get logged-in tutor user_id
$tutor_user_id = $_SESSION['user_id'] ?? 0;
if(!$tutor_user_id){
    header("Location: ../login.php");
    exit;
}

// Get tutor_profile_id
$stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=?");
$stmt->bind_param("i", $tutor_user_id);
$stmt->execute();
$tutor_profile = $stmt->get_result()->fetch_assoc();
$tutor_profile_id = $tutor_profile['id'] ?? 0;

// Optional search by status
$search_status = $_GET['status'] ?? '';

$query = "SELECT id, message, status, created_at FROM tutor_tickets WHERE tutor_profile_id=?";
$params = [$tutor_profile_id];
$types = "i";

if($search_status){
    $query .= " AND status=?";
    $params[] = $search_status;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<link rel="stylesheet" href="../assets/css/stud.css">

<div class="content">
    <h1>My Messages / Tickets</h1>

    <form method="GET" style="margin-bottom:15px;">
        <select name="status">
            <option value="">All Status</option>
            <option value="open" <?= $search_status=='open'?'selected':'' ?>>Open</option>
            <option value="resolved" <?= $search_status=='resolved'?'selected':'' ?>>Resolved</option>
            <option value="closed" <?= $search_status=='closed'?'selected':'' ?>>Closed</option>
        </select>
        <button type="submit" class="btn">Filter</button>
        <a href="new_ticket.php" class="btn">+ New Ticket</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Message</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <button class="btn btn-view" onclick="window.open('view_ticket.php?id=<?= $row['id'] ?>','Ticket','width=600,height=400')">View / Reply</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No messages found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
