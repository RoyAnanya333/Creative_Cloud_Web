<?php
include("../includes/header_admin.php");
include("../config/config.php");

$search = $_GET['search'] ?? '';
$search_param = "%$search%";

// Fetch courses with optional search
$stmt = $conn->prepare("
    SELECT 
        c.id, c.title, c.description, c.price,
        u.full_name AS tutor_name, u.contact_number AS tutor_contact,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS total_students,
        (SELECT IFNULL(SUM(p.amount),0) FROM payments p WHERE p.course_id = c.id AND p.status='paid') AS total_earning
    FROM courses c
    LEFT JOIN tutor_profiles tp ON c.tutor_profile_id = tp.id
    LEFT JOIN users u ON tp.user_id = u.id
    WHERE c.title LIKE ?
    ORDER BY c.id DESC
");
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<link rel="stylesheet" href="../assets/css/stud.css">

<div class="content">
    <h1>Manage Courses</h1>

    <form method="GET" style="margin-bottom:15px; display:flex; gap:10px; align-items:center;">
        <input type="text" name="search" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>" style="padding:6px; border:1px solid #cbd5e1; border-radius:4px;">
        <button type="submit" class="btn">Search</button>
        <a href="add_course.php" class="btn">+ Add Course</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>SL</th>
                <th>Course Title</th>
                <th>Description</th>
                <th>Tutor Name</th>
                <th>Tutor Contact</th>
                <th>Course Fee (BDT)</th>
                <th>Total Enrolled</th>
                <th>Total Earning (BDT)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if($result->num_rows > 0):
                $sl = 1;
                while($row = $result->fetch_assoc()): 
            ?>
                <tr>
                    <td><?= $sl++ ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars(substr($row['description'],0,50)) ?><?= strlen($row['description'])>50?'...':'' ?></td>
                    <td><?= htmlspecialchars($row['tutor_name'] ?? 'Unassigned') ?></td>
                    <td><?= htmlspecialchars($row['tutor_contact'] ?? '-') ?></td>
                    <td><?= number_format($row['price'],2) ?></td>
                    <td><?= $row['total_students'] ?></td>
                    <td><?= number_format($row['total_earning'],2) ?></td>
                    <td>
                        <a href="edit_course.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a> | 
                        <a href="delete_course.php?id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure to delete this course?');">Delete</a>
                    </td>
                </tr>
            <?php 
                endwhile; 
            else: 
            ?>
                <tr><td colspan="9" style="text-align:center;">No courses found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
.table th, .table td {
    border: 1px solid #cbd5e1;
    padding: 8px;
    text-align: left;
}
.table th {
    background-color: #1e3a8a;
    color: #fff;
}
.table tr:nth-child(even) { background-color: #f3f4f6; }

.btn-edit, .btn-delete {
    padding:4px 8px;
    border-radius:4px;
    color:#fff;
    text-decoration:none;
    font-size:0.9rem;
}
.btn-edit { background:#16a34a; }
.btn-edit:hover { background:#22c55e; }
.btn-delete { background:#ef4444; }
.btn-delete:hover { background:#b91c1c; }
</style>

<?php include("../includes/footer_admin.php"); ?>
