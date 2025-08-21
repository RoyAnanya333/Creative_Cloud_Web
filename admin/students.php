<?php
include("../includes/header_admin.php");
include("../config/config.php"); // Database connection

// Handle search
$search = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT id, full_name, username, email, contact_number, dob, nid, created_at 
                            FROM users 
                            WHERE user_type='student' AND is_active=1 
                            AND (full_name LIKE ? OR username LIKE ? OR email LIKE ? OR contact_number LIKE ?) 
                            ORDER BY id DESC");
    $param = "%" . $search . "%";
    $stmt->bind_param("ssss", $param, $param, $param, $param);
} else {
    $stmt = $conn->prepare("SELECT id, full_name, username, email, contact_number, dob, nid, created_at 
                            FROM users 
                            WHERE user_type='student' AND is_active=1 
                            ORDER BY id DESC");
}

$stmt->execute();
$result = $stmt->get_result();
?>
<link rel="stylesheet" href="../assets/CSS/stud.css">

<div class="content-wrapper">
    <div class="content">
        <div class="header-row">
            <h1>Manage Students</h1>
            <div class="actions">
                <a href="add_student.php" class="btn">+ Add Student</a>
                <form method="GET" action="" class="search-form">
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>DOB</th>
                    <th>NID</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['contact_number']) ?></td>
                        <td><?= htmlspecialchars($row['dob']) ?></td>
                        <td><?= htmlspecialchars($row['nid']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <a href="delete_student.php?id=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" style="text-align:center;">No students found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("../includes/footer_admin.php"); ?>
