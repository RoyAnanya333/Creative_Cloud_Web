<?php
include("../includes/header_admin.php");
include("../config/config.php");

$errors = [];
$success = '';

// ✅ Corrected typo: REQUEST_METHOD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $contact   = trim($_POST['contact_number']);
    $dob       = trim($_POST['dob']);
    $nid       = trim($_POST['nid']);
    $password  = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($_POST['password'])) {
        $errors[] = "Full Name, Username, Email, and Password are required.";
    }

    // Check for duplicate username/email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username or Email already exists.";
        }
    }

    // Insert data
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, contact_number, dob, nid, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'student')");
        $stmt->bind_param("sssssss", $full_name, $username, $email, $password, $contact, $dob, $nid);
        if ($stmt->execute()) {
            $success = "✅ Student added successfully!";
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>
<style>
    


    /* Container */
.content {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px 25px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Heading */
.content h1 {
    text-align: center;
    margin-bottom: 25px;
    color: #1e3a8a;
    font-size: 2rem;
}

/* Form */
.form {
    display: flex;
    flex-direction: column;
}

.form label {
    margin-top: 15px;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form input[type="text"],
.form input[type="email"],
.form input[type="password"],
.form input[type="date"] {
    padding: 10px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 1rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form input:focus {
    border-color: #1e3a8a;
    box-shadow: 0 0 5px rgba(30, 58, 138, 0.3);
    outline: none;
}

/* Submit button */
.form .btn {
    margin-top: 25px;
    padding: 12px 20px;
    background-color: #1e3a8a;
    color: #fff;
    font-size: 1.05rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}

.form .btn:hover {
    background-color: #374ccc;
    transform: translateY(-2px);
}

/* Error messages */
.error {
    background-color: #ffe6e6;
    border-left: 5px solid #ff4d4d;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.error p {
    margin: 5px 0;
    color: #b30000;
    font-size: 0.95rem;
}

/* Success message */
.success {
    background-color: #e6ffed;
    border-left: 5px solid #28a745;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    color: #155724;
    font-weight: 500;
    font-size: 1rem;
}

/* Responsive */
@media (max-width: 640px) {
    .content {
        padding: 20px 15px;
    }

    .form input,
    .form .btn {
        font-size: 0.95rem;
    }
}

    </style>
<!-- Load CSS -->

<link rel="stylesheet" href="../assets/CSS/stud.css">

<link rel="stylesheet" href="../assets/CSS/admin.css">
<div class="content">
    <h1>Add Student</h1>

    <?php if(!empty($errors)): ?>
        <div class="error">
            <?php foreach($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" class="form">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" placeholder="Enter full name" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter username" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter email" required>

        <label for="contact_number">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number" placeholder="Enter contact number">

        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob">

        <label for="nid">NID</label>
        <input type="text" id="nid" name="nid" placeholder="Enter NID number">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required>

        <button type="submit" class="btn">+ Add Student</button>
    </form>
</div>

<?php include("../includes/footer_admin.php"); ?>
