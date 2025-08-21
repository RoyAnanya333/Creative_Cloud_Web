<?php
include("../includes/header_admin.php");
include("../config/config.php");

$errors = [];

// Fetch categories
$categories = $conn->query("SELECT id, name FROM course_categories ORDER BY name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title']);
    $description  = trim($_POST['description']);
    $category_id  = $_POST['category_id'] ?: null;
    $level        = $_POST['level'] ?? 'All Levels';
    $duration_weeks = intval($_POST['duration_weeks'] ?? 1);
    $price        = floatval($_POST['price'] ?? 0);
    $tutor_id     = $_POST['tutor_id'] ?: null;
    $status       = $_POST['status'] ?? 'published';

    // Validate required fields
    if (!$title) $errors[] = "Course title is required.";
    if (!$description) $errors[] = "Course description is required.";

    // Handle banner upload
    $banner_path = null;
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
        $banner_name = 'course_' . time() . '.' . $ext;
        $target_dir = "../uploads/courses/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $target_file = $target_dir . $banner_name;
        if (move_uploaded_file($_FILES['banner']['tmp_name'], $target_file)) {
            $banner_path = "uploads/courses/" . $banner_name;
        } else {
            $errors[] = "Failed to upload banner image.";
        }
    }

    // Get tutor_profile_id
    $tutor_profile_id = null;
    if ($tutor_id) {
        $stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=?");
        $stmt->bind_param("i", $tutor_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if (!$res) {
            // Auto-create tutor profile if missing
            $conn->query("INSERT INTO tutor_profiles (user_id, bio) VALUES ($tutor_id, '')");
            $stmt2 = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=?");
            $stmt2->bind_param("i", $tutor_id);
            $stmt2->execute();
            $res = $stmt2->get_result()->fetch_assoc();
        }
        $tutor_profile_id = $res['id'];
    }

    // Insert course
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO courses 
            (title, description, category_id, level, duration_weeks, price, tutor_profile_id, status, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssisiidss",
            $title,
            $description,
            $category_id,
            $level,
            $duration_weeks,
            $price,
            $tutor_profile_id,
            $status,
            $banner_path
        );

        if ($stmt->execute()) {
            header("Location: courses.php");
            exit;
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>

<link rel="stylesheet" href="../assets/css/stud.css">

<div class="content">
    <h1>Add New Course</h1>

    <?php if ($errors): ?>
        <div class="error-box">
            <?php foreach ($errors as $err) echo "<p>$err</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="max-width:700px;">
        <label>Course Title</label>
        <input type="text" name="title" required>

        <label>Course Description</label>
        <textarea name="description" rows="5" required></textarea>

        <label>Category</label>
        <select name="category_id">
            <option value="">-- Select Category --</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Level</label>
        <select name="level">
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
            <option value="All Levels" selected>All Levels</option>
        </select>

        <label>Duration (weeks)</label>
        <input type="number" name="duration_weeks" min="1" value="1" required>

        <label>Course Fee (BDT)</label>
        <input type="number" name="price" min="0" step="0.01" value="0" required>

        <label>Assign Tutor by ID</label>
        <input type="number" name="tutor_id" id="tutor_id_input" placeholder="Enter Tutor ID">
        <button type="button" id="search_tutor_btn" class="btn" style="margin-top:5px;">Search Tutor</button>

        <div id="tutor_details" style="margin-top:10px; background:#f3f4f6; padding:10px; border-radius:5px; display:none;">
            <strong>Tutor Info:</strong>
            <p id="tutor_info_text"></p>
        </div>

        <label>Course Banner</label>
        <input type="file" name="banner" accept="image/*">
        <img id="banner_preview" src="#" style="display:none; max-width:250px; height:150px; object-fit:cover; margin-top:10px; border-radius:5px;" />

        <label>Status</label>
        <select name="status">
            <option value="published" selected>Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>

        <button type="submit" class="btn">Add Course</button>
    </form>
</div>
<Style>
    
<style>
form label{display:block;margin-top:10px;font-weight:bold;}
form input, form select, form textarea{width:100%;padding:8px;margin-top:4px;border:1px solid #cbd5e1;border-radius:4px;}
form button{margin-top:15px;padding:8px 15px;background:#1e3a8a;color:#fff;border:none;border-radius:4px;cursor:pointer;}
form button:hover{background:#3b82f6;}
.error-box{background:#fee2e2;color:#b91c1c;padding:10px;border-radius:5px;margin-bottom:15px;}




form label { display:block; margin-top:10px; font-weight:bold; }
form input, form select, form textarea { width:100%; padding:8px; margin-top:4px; border:1px solid #cbd5e1; border-radius:4px; }
form button { margin-top:10px; padding:8px 15px; background:#1e3a8a; color:#fff; border:none; border-radius:4px; cursor:pointer; }
form button:hover { background:#3b82f6; }
.error-box { background:#fee2e2; color:#b91c1c; padding:10px; border-radius:5px; margin-bottom:15px; }

form label {
    display:block;
    margin-top:10px;
    font-weight:bold;
}
form input, form select, form textarea {
    width:100%;
    padding:8px;
    margin-top:4px;
    border:1px solid #cbd5e1;
    border-radius:4px;
}
form button {
    margin-top:15px;
    padding:8px 15px;
    background:#1e3a8a;
    color:#fff;
    border:none;
    border-radius:4px;
    cursor:pointer;
}
form button:hover { background:#3b82f6; }

.error-box {
    background:#fee2e2;
    color:#b91c1c;
    padding:10px;
    border-radius:5px;
    margin-bottom:15px;
}

</style>

<script>
// Banner preview
document.querySelector('input[name="banner"]').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('banner_preview');
            img.src = e.target.result;
            img.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Fetch tutor details
document.getElementById('search_tutor_btn').addEventListener('click', function(){
    const id = document.getElementById('tutor_id_input').value;
    if(!id) return alert("Enter Tutor ID");

    fetch('fetch_tutor.php?tutor_id='+id)
    .then(res => res.json())
    .then(data => {
        const div = document.getElementById('tutor_details');
        const info = document.getElementById('tutor_info_text');
        div.style.display = 'block';
        if(data.error){
            info.innerHTML = "Error: "+data.error;
        } else {
            info.innerHTML = `
                ID: ${data.id} <br>
                Name: ${data.full_name} <br>
                Username: ${data.username} <br>
                Email: ${data.email} <br>
                Contact: ${data.contact_number} <br>
                Code: ${data.tutor_code || 'N/A'}
            `;
        }
    })
    .catch(err => {
        document.getElementById('tutor_details').style.display='block';
        document.getElementById('tutor_info_text').innerHTML = "Error fetching tutor info.";
    });
});
</script>

<?php include("../includes/footer_admin.php"); ?>
