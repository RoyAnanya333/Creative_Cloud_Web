<?php
include("../includes/header_admin.php");
include("../config/config.php");

$errors = [];

// Get course ID from GET
$course_id = $_GET['id'] ?? null;
if(!$course_id) {
    die("Course ID is required.");
}

// Fetch categories
$categories = $conn->query("SELECT id, name FROM course_categories ORDER BY name ASC");

// Fetch course info
$stmt = $conn->prepare("
    SELECT c.*, tp.user_id AS tutor_user_id, tp.tutor_code
    FROM courses c
    LEFT JOIN tutor_profiles tp ON tp.id = c.tutor_profile_id
    WHERE c.id=?
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
if(!$course) die("Course not found.");

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $level = $_POST['level'] ?? 'All Levels';
    $duration_weeks = $_POST['duration_weeks'] ?? 1;
    $price = $_POST['price'] ?? 0;
    $tutor_id = $_POST['tutor_id'] ?? null;
    $status = $_POST['status'] ?? 'published';

    // Banner upload
    $banner_path = $course['image_url'];
    if(isset($_FILES['banner']) && $_FILES['banner']['error']==0){
        $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
        $banner_name = 'course_'.time().'.'.$ext;
        $target_dir = "../uploads/courses/";
        if(!is_dir($target_dir)) mkdir($target_dir,0755,true);
        $target_file = $target_dir.$banner_name;
        if(move_uploaded_file($_FILES['banner']['tmp_name'],$target_file)){
            $banner_path = 'uploads/courses/'.$banner_name;
        } else {
            $errors[] = "Failed to upload banner image.";
        }
    }

    if(!$title) $errors[] = "Course title is required.";
    if(!$description) $errors[] = "Course description is required.";

    // Fetch tutor_profile_id
    $tutor_profile_id = null;
    if($tutor_id){
        $stmt = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=?");
        $stmt->bind_param("i",$tutor_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if(!$res){
            // Auto-create profile if missing
            $conn->query("INSERT INTO tutor_profiles (user_id) VALUES ($tutor_id)");
            $stmt2 = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=?");
            $stmt2->bind_param("i",$tutor_id);
            $stmt2->execute();
            $res = $stmt2->get_result()->fetch_assoc();
        }
        $tutor_profile_id = $res['id'];
    }

    if(empty($errors)){
        $stmt = $conn->prepare("
            UPDATE courses SET
            title=?, description=?, category_id=?, level=?, duration_weeks=?, price=?, tutor_profile_id=?, status=?, image_url=?
            WHERE id=?
        ");
        $stmt->bind_param("ssisiidssi",$title,$description,$category_id,$level,$duration_weeks,$price,$tutor_profile_id,$status,$banner_path,$course_id);

        if($stmt->execute()){
            header("Location: courses.php");
            exit;
        } else {
            $errors[] = "Database error: ".$conn->error;
        }
    }
}
?>

<link rel="stylesheet" href="../assets/CSS/stud.css">

<div class="content">
    <h1>Edit Course</h1>

    <?php if($errors): ?>
        <div class="error-box">
            <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="max-width:700px;">
        <label>Course Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>" readonly>

        <label>Course Description</label>
        <textarea name="description" rows="5" readonly><?= htmlspecialchars($course['description']) ?></textarea>



        <label>Duration (weeks)</label>
        <input type="number" name="duration_weeks" min="1" value="<?= $course['duration_weeks'] ?>" readonly>

        <label>Course Fee (BDT)</label>
        <input type="number" name="price" min="0" step="0.01" value="<?= $course['price'] ?>" required>

        <label>Assign Tutor by ID</label>
        <input type="number" name="tutor_id" id="tutor_id_input" value="<?= $course['tutor_user_id'] ?>" placeholder="Enter Tutor ID">

        <button type="button" id="search_tutor_btn" class="btn" style="margin-top:5px;">Search Tutor</button>

        <div id="tutor_details" style="margin-top:10px; background:#f3f4f6; padding:10px; border-radius:5px; display:none;">
            <strong>Tutor Info:</strong>
            <p id="tutor_info_text"></p>
        </div>

        <label>Course Banner</label>
        <input type="file" name="banner" accept="image/*">
        <?php if($course['image_url']): ?>
            <img id="banner_preview" src="../<?= $course['image_url'] ?>" style="display:block; max-width:200px; margin-top:10px;" />
        <?php else: ?>
            <img id="banner_preview" src="#" style="display:none; max-width:200px; margin-top:10px;" />
        <?php endif; ?>

        <label>Status</label>
        <select name="status">
            <?php
            $statuses = ['published','draft','archived'];
            foreach($statuses as $st):
                $selected = ($st==$course['status']) ? 'selected' : '';
            ?>
                <option value="<?= $st ?>" <?= $selected ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Update Course</button>
    </form>
</div>
<style>

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

    </Style>
<script>
document.getElementById('search_tutor_btn').addEventListener('click',function(){
    const id = document.getElementById('tutor_id_input').value;
    if(!id) return alert("Enter Tutor ID");

    fetch('fetch_tutor.php?tutor_id='+id)
        .then(res=>res.json())
        .then(data=>{
            if(data.error){
                document.getElementById('tutor_details').style.display='block';
                document.getElementById('tutor_info_text').innerHTML = "Error: "+data.error;
            } else {
                document.getElementById('tutor_details').style.display='block';
                document.getElementById('tutor_info_text').innerHTML =
                    `ID: ${data.id} <br>
                     Name: ${data.full_name} <br>
                     Username: ${data.username} <br>
                     Email: ${data.email} <br>
                     Contact: ${data.contact_number} <br>
                     Code: ${data.tutor_code}`;
            }
        })
        .catch(err=>{
            document.getElementById('tutor_details').style.display='block';
            document.getElementById('tutor_info_text').innerHTML = "Error fetching tutor info.";
        });
});

// Banner preview
document.querySelector('input[name="banner"]').addEventListener('change',function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            const img = document.getElementById('banner_preview');
            img.src = e.target.result;
            img.style.display='block';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include("../includes/footer_admin.php"); ?>
