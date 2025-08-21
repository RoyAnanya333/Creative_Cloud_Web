<?php
include("../includes/header_admin.php");  // Admin header + session
include("../config/config.php");          // DB connection

// Optional search by tutor name
$search = $_GET['search'] ?? '';
$search_param = "%$search%";

// Fetch tutor tickets with tutor info
$stmt = $conn->prepare("
    SELECT tt.id, tt.message, tt.status, tt.created_at, u.full_name
    FROM tutor_tickets tt
    JOIN tutor_profiles tp ON tt.tutor_profile_id = tp.id
    JOIN users u ON tp.user_id = u.id
    WHERE u.full_name LIKE ?
    ORDER BY tt.created_at DESC
");
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<link rel="stylesheet" href="../assets/css/stud.css">

<div class="content">
    <h1>Tutor Messages / Tickets</h1>

    <form method="GET" style="margin-bottom:15px;">
        <input type="text" name="search" placeholder="Search by tutor..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn">Search</button>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tutor Name</th>
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
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td><?= htmlspecialchars($row['message']) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <button class="btn btn-reply" onclick="openReplyPopup(<?= $row['id'] ?>)">Reply</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No messages found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function openReplyPopup(ticketId) {
    window.open('reply_ticket.php?id=' + ticketId, 'Reply', 'width=600,height=400,scrollbars=yes');
}
</script>

<?php include("../includes/footer_admin.php"); ?>
