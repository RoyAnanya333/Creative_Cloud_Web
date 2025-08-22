<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header_tutor.php';

// ------------------------------------------------------------
// AuthZ: only logged-in tutors
// ------------------------------------------------------------
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'tutor') {
    header('Location: ../guest/login.php');
    exit;
}

$tutor_user_id = (int) $_SESSION['user_id'];
$course_id     = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

if ($course_id <= 0) {
    echo '<div class="gc-wrap"><p>Course not found.</p></div>';
    require_once __DIR__ . '/../includes/tutor_footer.php';
    exit;
}

// Fetch tutor profile
$stmt = $conn->prepare('SELECT * FROM tutor_profiles WHERE user_id=? LIMIT 1');
$stmt->bind_param('i', $tutor_user_id);
$stmt->execute();
$tutor_profile = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$tutor_profile) {
    echo '<div class="gc-wrap"><p>No tutor profile found. Contact admin.</p></div>';
    require_once __DIR__ . '/../includes/tutor_footer.php';
    exit;
}

// ------------------------------------------------------------
// Enrollment guard: check course belongs to this tutor
// ------------------------------------------------------------
$stmt = $conn->prepare('SELECT * FROM courses WHERE id=? AND tutor_profile_id=?');
$stmt->bind_param('ii', $course_id, $tutor_profile['id']);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$course) {
    echo '<div class="gc-wrap"><p>Course not found or you are not assigned to this course.</p></div>';
    require_once __DIR__ . '/../includes/tutor_footer.php';
    exit;
}

// ------------------------------------------------------------
// CSRF helper
// ------------------------------------------------------------
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$CSRF = $_SESSION['csrf'];
function csrf_ok(string $key='csrf'): bool {
    return isset($_POST[$key]) && hash_equals($_SESSION['csrf'] ?? '', $_POST[$key]);
}

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function save_upload(string $field, string $destDir): array {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return [null, 'Upload error code: ' . $_FILES[$field]['error']];
    if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
    $orig = basename($_FILES[$field]['name']);
    $ext  = pathinfo($orig, PATHINFO_EXTENSION);
    $safe = uniqid('att_', true) . ($ext ? ('.'.$ext) : '');
    $path = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $safe;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $path)) return [null, 'Failed to save upload'];
    return [$path, null];
}

$errors = []; $notices = [];

// ------------------------------------------------------------
// POST actions: add_post, add_classwork
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Add post (announcement)
    if ($action === 'add_post' && csrf_ok()) {
        $content = trim($_POST['content'] ?? '');
        if ($content === '') $errors[] = 'Post content is required.';
        [$filePath, $uerr] = save_upload('attachment', __DIR__ . '/../uploads');
        if ($uerr) $errors[] = $uerr;
        if (!$errors) {
            $att = $filePath ? str_replace(__DIR__.'/..','..',$filePath) : null;
            $stmt = $conn->prepare('INSERT INTO posts (course_id, tutor_profile_id, author_user_id, content, attachment_url) VALUES (?,?,?,?,?)');
            $stmt->bind_param('iiiss', $course_id, $tutor_profile['id'], $tutor_user_id, $content, $att);
            $stmt->execute(); $stmt->close();
            $notices[] = 'Posted to stream.';
        }
    }

    // 2. Add classwork/assignment
    if ($action === 'add_classwork' && csrf_ok()) {
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $type  = $_POST['work_type'] ?? 'material';
        $due   = $_POST['due_at'] ?? null;
        [$filePath, $uerr] = save_upload('attachment', __DIR__.'/../uploads');
        if ($uerr) $errors[] = $uerr;
        if ($title==='') $errors[] = 'Title is required.';
        if (!$errors) {
            $att = $filePath ? str_replace(__DIR__.'/..','..',$filePath) : null;
            $stmt = $conn->prepare('INSERT INTO classwork (course_id, title, description, work_type, due_at, attachment_url, status, created_at) VALUES (?,?,?,?,?,?,\'active\',NOW())');
            $stmt->bind_param('sssss', $course_id, $title, $desc, $type, $due, $att);
            $stmt->execute(); $stmt->close();
            $notices[] = 'Classwork added.';
        }
    }
}

// ------------------------------------------------------------
// Fetch data for tabs
// ------------------------------------------------------------
$tab = $_GET['tab'] ?? 'stream';

// Stream posts
$stream_posts = [];
if ($tab==='stream') {
    $stmt = $conn->prepare(
        'SELECT p.id, p.content, p.attachment_url, p.created_at, u.full_name AS author
         FROM posts p
         JOIN users u ON u.id = p.author_user_id
         WHERE p.course_id = ?
         ORDER BY p.created_at DESC'
    );
    $stmt->bind_param('i',$course_id);
    $stmt->execute();
    $stream_posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Classwork list
$classwork = [];
if ($tab==='classwork') {
    $stmt = $conn->prepare('SELECT * FROM classwork WHERE course_id=? AND status<>\'draft\' ORDER BY COALESCE(due_at, created_at) DESC');
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $classwork = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// People list
$people = ['students'=>[], 'teacher'=>null];
if ($tab==='people') {
    $people['teacher'] = $_SESSION['full_name'];
    $stmt = $conn->prepare('SELECT u.full_name, u.username FROM enrollments e JOIN users u ON u.id = e.student_id WHERE e.course_id=? ORDER BY u.full_name ASC');
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $people['students'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

?>

<link rel="stylesheet" href="../assets/css/main.css">
<!-- Optional: reuse the student GC CSS -->
<style>

    <style>


    /* Stream Box */
.stream-post-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    padding: 15px;
}

/* Tabs */
.post-type-tabs {
    display: flex;
    list-style: none;
    margin: 0 0 10px;
    padding: 0;
    border-bottom: 2px solid #eee;
}
.post-type-tabs li {
    padding: 8px 16px;
    cursor: pointer;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    transition: 0.3s;
}
.post-type-tabs li.active {
    border-bottom: 2px solid #4285f4;
    color: #4285f4;
}

/* Form Areas */
.post-form-content {
    display: none;
}
.post-form-content.active {
    display: block;
}
.post-form textarea, 
.post-form input[type="text"], 
.post-form input[type="date"] {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    margin: 8px 0;
    font-size: 14px;
}

/* Upload */
.upload-label {
    display: inline-block;
    background: #f1f3f4;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
}
.upload-label:hover {
    background: #e8eaed;
}

/* Post Button */
.post-btn {
    background: #4285f4;
    border: none;
    color: #fff;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
    float: right;
    margin-top: 10px;
}
.post-btn:hover {
    background: #3367d6;
}

/* Posts Feed */
.posts-feed {
    margin-top: 20px;
}
.post-card {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.post-header {
    font-size: 13px;
    color: #555;
    margin-bottom: 6px;
}
.post-body {
    font-size: 15px;
    margin-bottom: 8px;
}
.post-attachment a {
    color: #4285f4;
    font-weight: 500;
    text-decoration: none;
}

:root{
  --gc-primary:#1a73e8; --gc-bg:#f6f8fc; --gc-surface:#fff; --gc-text:#202124; --gc-muted:#5f6368;
  --gc-chip:#e8f0fe; --gc-danger:#d93025; --gc-success:#188038; --gc-border:#e0e3e7;
}
.gc-hero{background:linear-gradient(120deg,#3f72ff,#7bc3ff);border-radius:16px;color:#fff;padding:28px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:0 6px 20px rgba(63,114,255,.25)}
.gc-hero h2{margin:0;font-size:28px;font-weight:700}
.gc-hero .meta{opacity:.95}
.gc-tabs{display:flex;gap:8px;margin:16px 0 12px;border-bottom:1px solid var(--gc-border)}
.gc-tab{padding:10px 14px;border-radius:10px 10px 0 0;text-decoration:none;color:var(--gc-muted);font-weight:600}
.gc-tab.active{color:var(--gc-primary);background:var(--gc-chip)}
.gc-grid{display:grid;grid-template-columns:1fr;gap:12px}
.gc-card{background:var(--gc-surface);border:1px solid var(--gc-border);border-radius:14px;padding:16px;box-shadow:0 2px 6px rgba(0,0,0,.04)}
.gc-post-head{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.gc-avatar{width:36px;height:36px;border-radius:50%;background:#cbd5e1}
.gc-author{font-weight:600;color:var(--gc-text)}
.gc-time{color:var(--gc-muted);font-size:12px}
.gc-post-body{white-space:pre-wrap;color:var(--gc-text);margin:6px 0 8px}
.gc-attach a{font-size:14px}
.gc-comment{margin-top:10px;padding:10px;border-top:1px solid var(--gc-border)}
.gc-comment p{margin:0}
.gc-comment .by{font-weight:600;margin-right:6px}
.gc-form{display:flex;gap:8px;margin-top:8px}
.gc-form textarea{flex:1;min-height:60px;padding:10px;border:1px solid var(--gc-border);border-radius:10px}
.gc-form input[type=file]{font-size:12px}
.gc-form .btn{background:var(--gc-primary);color:#fff;border:none;border-radius:10px;padding:10px 14px;cursor:pointer}
.gc-form .btn.secondary{background:#eef3fd;color:#1a73e8;border:1px solid #d2e3fc}
.gc-kv{display:flex;gap:10px;flex-wrap:wrap}
.gc-chip{background:var(--gc-chip);padding:4px 8px;border-radius:999px;font-size:12px}
.gc-section-title{font-size:18px;font-weight:700;margin:6px 0 4px}
.gc-subtle{color:var(--gc-muted);font-size:13px}
.gc-two{display:grid;grid-template-columns:2fr 1fr;gap:12px}
@media(max-width:900px){.gc-two{grid-template-columns:1fr}}
.gc-status{font-size:12px;padding:4px 8px;border-radius:999px;border:1px solid var(--gc-border)}
.gc-status.submitted{background:#e6f4ea;color:var(--gc-success);border-color:#cde9d6}
.gc-status.late{background:#fce8e6;color:var(--gc-danger);border-color:#fad2cf}
.gc-list{list-style:none;margin:0;padding:0}
.gc-list li{padding:10px 0;border-bottom:1px solid var(--gc-border)}
.gc-toast{margin:12px 0;padding:10px 12px;border-radius:10px;border:1px solid var(--gc-border);background:#fff}
.gc-toast.error{border-color:#f1bcbc;background:#fff5f5}
.gc-toast.ok{border-color:#cde9d6;background:#f6fffa}
</style>


<div class="gc-wrap">
  <div class="gc-hero">
    <div>
      <h2><?= h($course['title']) ?></h2>
      <div class="meta">Tutor: <?= h($_SESSION['full_name']) ?></div>
    </div>
    <div class="gc-kv">
      <span class="gc-chip">Course ID #<?= (int)$course_id ?></span>
      <?php if(!empty($course['level'])): ?><span class="gc-chip"><?= h($course['level']) ?></span><?php endif; ?>
      <?php if(!empty($course['duration_weeks'])): ?><span class="gc-chip"><?= (int)$course['duration_weeks'] ?> weeks</span><?php endif; ?>
    </div>
  </div>

  <?php foreach($errors as $e): ?>
    <div class="gc-toast error">⚠️ <?= h($e) ?></div>
  <?php endforeach; ?>
  <?php foreach($notices as $n): ?>
    <div class="gc-toast ok">✅ <?= h($n) ?></div>
  <?php endforeach; ?>

  <div class="gc-tabs">
    <?php $cur=$tab; ?>
    <a class="gc-tab <?= $cur==='stream'?'active':'' ?>" href="?course_id=<?= $course_id ?>&tab=stream">Stream</a>
    <a class="gc-tab <?= $cur==='people'?'active':'' ?>" href="?course_id=<?= $course_id ?>&tab=people">People</a>
  </div>

  <?php if($tab==='stream'): ?>
    <div class="gc-two">
      <div>
        <!-- Share box (tutor can post) -->
        <div class="gc-card">
          <div class="gc-post-head">
            <div class="gc-avatar"></div>
            <div>
              <div class="gc-author">Share an announcement with your class</div>
              <div class="gc-time gc-subtle">Students will see it immediately</div>
            </div>
          </div>
          <form class="gc-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">
            <input type="hidden" name="action" value="add_post">
            <textarea name="content" placeholder="Write an announcement..."></textarea>
            <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
              <input type="file" name="attachment" accept="*/*">
              <button class="btn" type="submit">Post</button>
            </div>
          </form>
        </div>

        <?php if(empty($stream_posts)): ?>
          <div class="gc-card gc-subtle">No posts yet.</div>
        <?php endif; ?>

        <?php foreach($stream_posts as $p): ?>
          <div class="gc-card">
            <div class="gc-post-head">
              <div class="gc-avatar"></div>
              <div>
                <div class="gc-author"><?= h($p['author']) ?></div>
                <div class="gc-time"><?= h($p['created_at']) ?></div>
              </div>
            </div>
            <div class="gc-post-body"><?= nl2br(h($p['content'])) ?></div>
            <?php if(!empty($p['attachment_url'])): ?>
              <div class="gc-attach"><a target="_blank" href="<?= h($p['attachment_url']) ?>">📎 Attachment</a></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div>

          </ul>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if($tab==='classwork'): ?>
    <div class="gc-grid">
      <!-- Add Classwork form -->
      <div class="gc-card">
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">
          <input type="hidden" name="action" value="add_classwork">
          <input type="text" name="title" placeholder="Classwork title" required>
          <textarea name="description" placeholder="Description (optional)"></textarea>
          <select name="work_type">
            <option value="material">Material</option>
            <option value="assignment">Assignment</option>
            <option value="quiz">Quiz</option>
          </select>
          <input type="datetime-local" name="due_at">
          <input type="file" name="attachment">
          <button class="btn" type="submit">Add Classwork</button>
        </form>
      </div>

      <?php if(empty($classwork)): ?>
        <div class="gc-card gc-subtle">No classwork yet.</div>
      <?php endif; ?>

      <?php foreach($classwork as $cw): ?>
        <div class="gc-card">
          <div class="gc-section-title"><?= h($cw['title']) ?> <?= $cw['work_type']==='quiz'?'📝':($cw['work_type']==='material'?'📎':'📘') ?></div>
          <?php if(!empty($cw['description'])): ?><div class="gc-post-body"><?= nl2br(h($cw['description'])) ?></div><?php endif; ?>
          <div class="gc-subtle">
            <?php if(!empty($cw['due_at'])): ?>Due: <?= h($cw['due_at']) ?> · <?php endif; ?>
          </div>
          <?php if(!empty($cw['attachment_url'])): ?>
            <div class="gc-attach"><a target="_blank" href="<?= h($cw['attachment_url']) ?>">Attachment</a></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if($tab==='people'): ?>
    <div class="gc-two">
      <div class="gc-card">
        <div class="gc-section-title">Tutor</div>
        <p><?= h($_SESSION['full_name']) ?></p>
      </div>
      <div class="gc-card">
        <div class="gc-section-title">Enrolled Students (<?= count($people['students']) ?>)</div>
        <ul class="gc-list">
          <?php foreach($people['students'] as $s): ?>
            <li><?= h($s['full_name']) ?> <span class="gc-subtle">(@<?= h($s['username']) ?>)</span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>
</div>

