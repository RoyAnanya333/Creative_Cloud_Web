<?php include("../includes/header.php"); ?>
<link rel="stylesheet" href="../assets/style.css">
<div class="container">
    <h1>My Profile</h1>
    <?php
    $id = $_SESSION['user_id'];
    $res = $conn->query("SELECT * FROM users WHERE id=$id");
    $admin = $res->fetch_assoc();
    ?>
    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo $admin['username']; ?>" />
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $admin['email']; ?>" />
        <button type="submit" class="btn">Update</button>
    </form>
</div>
<?php include("../includes/footer.php"); ?>
