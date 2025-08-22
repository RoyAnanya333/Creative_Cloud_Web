<?php
include("../includes/header_admin.php");
include("../config/config.php"); // DB connection

$errors = [];
$success = '';

// Fetch next auto-increment ID for tutors
$result = $conn->query("SHOW TABLE STATUS LIKE 'users'");
$row = $result->fetch_assoc();
$next_tutor_id = $row['Auto_increment']; // This is the next ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $contact   = trim($_POST['contact_number']);
    $dob       = trim($_POST['dob']);
    $nid       = trim($_POST['nid']);
    $password  = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $salary    = trim($_POST['salary']);

    // Validation
    if(empty($full_name) || empty($username) || empty($email) || empty($password)){
        $errors[] = "Full Name, Username, Email, and Password are required.";
    }
    if(!is_numeric($salary) || $salary < 0){
        $errors[] = "Salary must be a positive number.";
    }

    // Check duplicates
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0){
        $errors[] = "Username or Email already exists.";
    }

    if(empty($errors)){
        // Insert tutor
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, contact_number, dob, nid, user_type, is_active, salary) VALUES (?, ?, ?, ?, ?, ?, ?, 'tutor', 1, ?)");
        $stmt->bind_param("sssssssi", $full_name, $username, $email, $password, $contact, $dob, $nid, $salary);
        if($stmt->execute()){
            $success = "Tutor added successfully! Tutor ID: ".$stmt->insert_id;
        } else {
            $errors[] = "Database error: ".$conn->error;
        }
    }
}
?>

<link rel="stylesheet" href="../assets/CSS/stud.css">
<link rel="stylesheet" href="../assets/CSS/admin.css">
<style>
    /* Reset */
*,
*:before,
*:after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f6f8fc;
    color: #202124;
}

/* Content wrapper */
.content {
    margin-left: 220px; /* space for sidebar */
    padding: 24px;
    padding-top: 80px; /* leave space for fixed header */
    min-height: calc(100vh - 60px);
}

/* Headings */
.content h1 {
    font-size: 1.8rem;
    margin-bottom: 20px;
    color: #1a73e8;
}

/* Form */
.form {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    max-width: 600px;
}

.form label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    margin-top: 12px;
    color: #333;
}

.form input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 1rem;
    margin-bottom: 6px;
    transition: border-color 0.2s;
}

.form input:focus {
    outline: none;
    border-color: #1a73e8;
}

/* Buttons */
.btn {
    margin-top: 16px;
    padding: 12px 20px;
    background: #1a73e8;
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn:hover {
    background: #1666c1;
}

/* Success & Error messages */
.success {
    background: #e6f4ea;
    color: #188038;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.error {
    background: #fce8e6;
    color: #b00020;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}

/* Responsive */
@media (max-width: 900px) {
    .content {
        margin-left: 0;
        padding-top: 140px; /* header + sidebar stacked */
    }

    .form {
        width: 100%;
        padding: 16px;
    }
}
</Style>
<div class="content">
    <h1>Add Tutor</h1>

    <?php if(!empty($errors)): ?>
        <div class="error">
            <?php foreach($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if($success) echo "<div class='success'>$success</div>"; ?>

    <form method="POST" class="form">
        <label>Tutor ID</label>
        <input type="text" value="<?= htmlspecialchars($next_tutor_id) ?>" readonly><br>

        <label>Full Name</label>
        <input type="text" name="full_name" placeholder="Full Name" required><br>

        <label>Username</label>
        <input type="text" name="username" placeholder="Username" required><br>

        <label>Email</label>
        <input type="email" name="email" placeholder="Email" required><br>

        <label>Contact Number</label>
        <input type="text" name="contact_number" placeholder="Contact Number"><br>

        <label>Date of Birth</label>
        <input type="date" name="dob" placeholder="Date of Birth"><br>

        <label>NID</label>
        <input type="text" name="nid" placeholder="NID"><br>

        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required><br>

        <label>Salary (BDT)</label>
        <input type="number" name="salary" placeholder="Salary" min="0" required><br>

        <button type="submit" class="btn">Add Tutor</button>
    </form>
</div>

<?php include("../includes/footer_admin.php"); ?>
