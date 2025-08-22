<?php
include("../includes/header_admin.php"); // Admin header + session
include("../config/config.php"); // Database connection

$search = $_GET['search'] ?? '';

// Prepare query with search filter
if($search){
    $stmt = $conn->prepare("SELECT id, full_name, username, email, contact_number, dob, nid, is_active, created_at FROM users WHERE user_type='tutor' AND (full_name LIKE ? OR username LIKE ? OR email LIKE ?) ORDER BY id DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
}else{
    $stmt = $conn->prepare("SELECT id, full_name, username, email, contact_number, dob, nid, is_active, created_at FROM users WHERE user_type='tutor' ORDER BY id DESC");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<link rel="stylesheet" href="../assets/CSS/stud.css">

<div class="content">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Manage Tutors</h1>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="add_tutor.php" class="btn">+ Add Tutor</a>
            <form method="GET" style="margin:0;">
                <input type="text" name="search" placeholder="Search tutors..." value="<?= htmlspecialchars($search) ?>" class="input-search">
                <button type="submit" class="btn btn-search">Search</button>
            </form>
        </div>
    </div>
<Style>
    /* Table action buttons */
.btn-edit,
.btn-restrict,
.btn-delete {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    transition: background 0.2s, color 0.2s;
    margin-right: 4px;
}

/* Edit button - blue */
.btn-edit {
    background-color: #3b82f6;
    color: #fff;
}

.btn-edit:hover {
    background-color: #1d4ed8;
}

/* Restrict / Unrestrict button - amber/orange */
.btn-restrict {
    background-color: #facc15;
    color: #1f2937;
}

.btn-restrict:hover {
    background-color: #eab308;
}

/* Delete button - red */
.btn-delete {
    background-color: #ef4444;
    color: #fff;
}

.btn-delete:hover {
    background-color: #b91c1c;
}

/* Optional: smaller screens table adjustments */
@media (max-width: 900px) {
    .btn-edit,
    .btn-restrict,
    .btn-delete {
        padding: 5px 8px;
        font-size: 0.8rem;
        margin-bottom: 4px;
    }
}


/* Make action buttons align in a single row */
td > .btn-edit,
td > .btn-restrict,
td > .btn-delete {
    display: inline-block;
    margin-right: 6px;
    margin-bottom: 0; /* remove any bottom margin so they stay in one line */
}

/* Optional: table cell flex for better alignment */
td:last-child {
    display: flex;
    flex-wrap: nowrap; /* prevent wrapping */
    gap: 6px; /* space between buttons */
    align-items: center;
}

</style>
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
                <th>Status</th>
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
                    <td><?= $row['is_active'] ? 'Active' : 'Restricted' ?></td>
                    <td>
                         <a href="edit_tutor.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>

                         
                        <button class="btn btn-restrict" onclick="openModal('<?= $row['id'] ?>', '<?= $row['is_active'] ? 'restrict' : 'unrestrict' ?>')">
                            <?= $row['is_active'] ? 'Restrict' : 'Unrestrict' ?>
                        </button>
                        <button class="btn btn-delete" onclick="openModal('<?= $row['id'] ?>', 'delete')">Remove</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9" style="text-align:center;">No tutors found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="actionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <p id="modal-text">Are you sure?</p>
        <div style="text-align:right;margin-top:10px;">
            <button onclick="confirmAction()" class="btn btn-confirm">Yes</button>
            <button onclick="closeModal()" class="btn btn-cancel">No</button>
        </div>
    </div>
</div>

<script>
let currentAction = '';
let currentId = '';

function openModal(id, action) {
    currentId = id;
    currentAction = action;
    const text = action === 'delete' ? 
        'Are you sure you want to remove this tutor?' : 
        'Are you sure you want to ' + action + ' this tutor?';
    document.getElementById('modal-text').innerText = text;
    document.getElementById('actionModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('actionModal').style.display = 'none';
    currentId = '';
    currentAction = '';
}

function confirmAction() {
    if(currentAction && currentId){
        window.location.href = currentAction + '_tutor.php?id=' + currentId;
    }
}
</script>

<style>
/* Search & buttons styling */
.input-search {
    padding:6px 10px;
    border:1px solid #cbd5e1;
    border-radius:4px;
    outline:none;
}

.btn-search {
    padding:6px 12px;
    background:#1e3a8a;
    color:#fff;
    border:none;
    border-radius:4px;
    cursor:pointer;
}

.btn-search:hover {
    background:#3b82f6;
}

/* Modal styles */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5); 
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; 
    padding: 20px;
    border-radius: 8px;
    width: 400px; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.close {
    color: #aaa;
    float: right;
    font-size: 1.2rem;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.btn-confirm {
    background-color: #ef4444;
    color: white;
    padding: 5px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-cancel {
    background-color: #6b7280;
    color: white;
    padding: 5px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-confirm:hover { background-color: #b91c1c; }
.btn-cancel:hover { background-color: #4b5563; }
</style>

<?php include("../includes/footer_admin.php"); ?>
