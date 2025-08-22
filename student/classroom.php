<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/student_header.php';

/**
 * AuthZ: only logged-in students
 */
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'student') {
    header('Location: ../guest/login.php');
    exit;
}

$student_id = (int) $_SESSION['user_id'];
$course_id  = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

if ($course_id <= 0) {
    echo '<div class="gc-wrap"><p>Course not found.</p></div>';
    require_once __DIR__ . '/../includes/student_footer.php';
    exit;
}

/**
 * CSRF token
 */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$CSRF = $_SESSION['csrf'];
function csrf_ok(string $key='csrf'): bool {
    return isset($_POST[$key]) && hash_equals($_SESSION['csrf'] ?? '', $_POST[$key]);
}

/**
 * Enrollment guard
 */
$stmt = $conn->prepare('SELECT 1 FROM enrollments WHERE course_id=? AND student_id=?');
$stmt->bind_param('ii', $course_id, $student_id);
$stmt->execute();
$stmt->store_result();
$is_enrolled = $stmt->num_rows > 0;
$stmt->close();

if (!$is_enrolled) {
    echo '<div class="gc-wrap"><p>You are not enrolled in this course.</p></div>';
    require_once __DIR__ . '/../includes/student_footer.php';
    exit;
}

/**
 * Fetch course + teacher
 * courses.tutor_profile_id → tutor_profiles.user_id → users (teacher)
 */
$stmt = $conn->prepare(
    'SELECT c.*, c.level, c.duration_weeks,
            tp.id AS tutor_profile_id,
            u.id AS teacher_user_id,
            u.full_name AS teacher_name,
            u.username AS teacher_username
     FROM courses c
     LEFT JOIN tutor_profiles tp ON tp.id = c.tutor_profile_id
     LEFT JOIN users u ON u.id = tp.user_id
     WHERE c.id = ?'
);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    echo '<div class="gc-wrap"><p>Course not found.</p></div>';
    require_once __DIR__ . '/../includes/student_footer.php';
    exit;
}

/** helpers */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/** uploads — used ONLY for assignment submission (not for stream/materials) */
function save_upload(string $field, string $destDir): array {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return [null, 'Upload error code: ' . $_FILES[$field]['error']];
    if (!is_dir($destDir)) @mkdir($destDir, 0775, true);

    $orig = basename($_FILES[$field]['name']);
    $ext  = pathinfo($orig, PATHINFO_EXTENSION);
    $safe = uniqid('sub_', true) . ($ext ? ('.' . $ext) : '');
    $path = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $safe;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $path)) return [null, 'Failed to save upload'];
    return [$path, null];
}

$errors  = [];
$notices = [];

/**
 * POST actions (students cannot create posts/materials)
 * - add_comment (stream comments)
 * - submit_assignment (turn in)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_comment') {
        if (!csrf_ok()) $errors[] = 'Invalid request (CSRF).';
        $post_id = (int)($_POST['post_id'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($post_id <= 0) $errors[] = 'Invalid post.';
        if ($comment === '') $errors[] = 'Comment cannot be empty.';

        // validate post belongs to this course
        if (!$errors) {
            $s = $conn->prepare('SELECT 1 FROM posts WHERE id=? AND course_id=?');
            $s->bind_param('ii', $post_id, $course_id);
            $s->execute(); $s->store_result();
            if ($s->num_rows === 0) $errors[] = 'Post not found.';
            $s->close();
        }
        if (!$errors) {
            $stmt = $conn->prepare('INSERT INTO post_comments (post_id, user_id, comment) VALUES (?,?,?)');
            $stmt->bind_param('iis', $post_id, $student_id, $comment);
            $stmt->execute();
            $stmt->close();
            $notices[] = 'Comment added.';
        }
    }

    if ($action === 'submit_assignment') {
        if (!csrf_ok()) $errors[] = 'Invalid request (CSRF).';
        $classwork_id = (int)($_POST['classwork_id'] ?? 0);
        $text_answer  = trim($_POST['text_answer'] ?? '');
        $now = date('Y-m-d H:i:s');

        // validate classwork
        $s = $conn->prepare("SELECT id, work_type, due_at FROM classwork WHERE id=? AND course_id=? AND status <> 'draft'");
        $s->bind_param('ii', $classwork_id, $course_id);
        $s->execute();
        $cw = $s->get_result()->fetch_assoc();
        $s->close();

        if (!$cw) $errors[] = 'Classwork not found.';
        if ($cw && $cw['work_type'] !== 'assignment') $errors[] = 'Only assignments can be submitted.';

        [$filePath, $uerr] = save_upload('file_url', __DIR__ . '/../uploads');
        if ($uerr) $errors[] = $uerr;
        $fileRel = $filePath ? str_replace(__DIR__ . '/..', '..', $filePath) : null;

        if (!$errors) {
            // upsert one submission per student
            $upd = $conn->prepare("
                UPDATE submissions
                   SET submitted_at=?, file_url=IFNULL(?, file_url), text_answer=?,
                       status=CASE WHEN ? > due_at THEN 'late' ELSE 'submitted' END
                 WHERE classwork_id=? AND student_id=?");
            $upd->bind_param('ssssii', $now, $fileRel, $text_answer, $now, $classwork_id, $student_id);
            $upd->execute();
            if ($upd->affected_rows === 0) {
                $upd->close();
                $ins = $conn->prepare("
                    INSERT INTO submissions (classwork_id, student_id, submitted_at, file_url, text_answer, status)
                    VALUES (?,?,?,?,?, 'submitted')");
                $ins->bind_param('iisss', $classwork_id, $student_id, $now, $fileRel, $text_answer);
                $ins->execute();
                $ins->close();
                // adjust late if needed
                $conn->query("UPDATE submissions s JOIN classwork c ON c.id=s.classwork_id
                              SET s.status='late'
                              WHERE s.classwork_id={$classwork_id} AND s.student_id={$student_id}
                                AND c.due_at IS NOT NULL AND s.submitted_at > c.due_at");
            } else {
                $upd->close();
            }
            $notices[] = 'Assignment submitted.';
        }
    }
}

/** active tab */
$tab = $_GET['tab'] ?? 'stream';

/** stream posts */
$stream_posts = [];
if ($tab === 'stream') {
    $stmt = $conn->prepare(
        'SELECT p.id, p.content, p.attachment_url, p.created_at,
                u.full_name AS author
         FROM posts p
         JOIN users u ON u.id = p.author_user_id
         WHERE p.course_id = ?
         ORDER BY p.created_at DESC'
    );
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $stream_posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

/** classwork list */
$classwork = [];
if ($tab === 'classwork') {
    $stmt = $conn->prepare(
        "SELECT id, title, description, work_type, due_at, max_points, attachment_url, status, created_at
         FROM classwork
         WHERE course_id=? AND status <> 'draft'
         ORDER BY COALESCE(due_at, created_at) DESC"
    );
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $classwork = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

/** people tab data */
$people = ['students'=>[], 'teacher'=>null];
if ($tab === 'people') {
    $people['teacher'] = $course['teacher_name'] ?: 'Not Assigned';
    $stmt = $conn->prepare(
        'SELECT u.full_name, u.username
           FROM enrollments e
           JOIN users u ON u.id = e.student_id
          WHERE e.course_id=?
          ORDER BY u.full_name ASC'
    );
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $people['students'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<link rel="stylesheet" href="../assets/css/main.css">
<style>
:root{
  --gc-primary:#1a73e8; --gc-bg:#f6f8fc; --gc-surface:#fff; --gc-text:#202124; --gc-muted:#5f6368;
  --gc-chip:#e8f0fe; --gc-danger:#d93025; --gc-success:#188038; --gc-border:#e0e3e7;
}
body{background:var(--gc-bg);} .gc-wrap{max-width:1100px;margin:24px auto;padding:0 16px;}
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
      <div class="meta">Teacher: <?= h($course['teacher_name'] ?: 'Not Assigned') ?></div>
    </div>
    <div class="gc-kv">
      <span class="gc-chip">Course ID #<?= (int)$course_id ?></span>
      <?php if(!empty($course['level'])): ?><span class="gc-chip"><?= h($course['level']) ?></span><?php endif; ?>
      <?php if(!empty($course['duration_weeks'])): ?><span class="gc-chip"><?= (int)$course['duration_weeks'] ?> weeks</span><?php endif; ?>
    </div>
  </div>

  <?php foreach ($errors as $e): ?>
      <div class="gc-toast error">⚠️ <?= h($e) ?></div>
  <?php endforeach; ?>
  <?php foreach ($notices as $n): ?>
      <div class="gc-toast ok">✅ <?= h($n) ?></div>
  <?php endforeach; ?>

  <div class="gc-tabs">
    <?php $cur=$tab; ?>
    <a class="gc-tab <?= $cur==='stream'?'active':'' ?>" href="?course_id=<?= (int)$course_id ?>&tab=stream">Stream</a>
    <a class="gc-tab <?= $cur==='classwork'?'active':'' ?>" href="?course_id=<?= (int)$course_id ?>&tab=classwork">Classwork</a>
    <a class="gc-tab <?= $cur==='people'?'active':'' ?>" href="?course_id=<?= (int)$course_id ?>&tab=people">People</a>
  </div>

  <?php if ($tab === 'stream'): ?>
    <div class="gc-two">
      <div>
        <!-- NOTE: Students cannot create posts/materials. Stream shows posts; students can comment. -->
        <?php if (empty($stream_posts)): ?>
          <div class="gc-card gc-subtle">No posts yet.</div>
        <?php endif; ?>

        <?php foreach ($stream_posts as $p): ?>
          <div class="gc-card">
            <div class="gc-post-head">
              <div class="gc-avatar"></div>
              <div>
                <div class="gc-author"><?= h($p['author']) ?></div>
                <div class="gc-time"><?= h($p['created_at']) ?></div>
              </div>
            </div>
            <div class="gc-post-body"><?= nl2br(h($p['content'])) ?></div>
            <?php if (!empty($p['attachment_url'])): ?>
              <div class="gc-attach"><a target="_blank" href="<?= h($p['attachment_url']) ?>">📎 Attachment</a></div>
            <?php endif; ?>

            <?php
              $pid = (int)$p['id'];
              $cstmt = $conn->prepare('
                    SELECT pc.comment, pc.created_at, u.full_name
                      FROM post_comments pc
                      JOIN users u ON u.id=pc.user_id
                     WHERE pc.post_id=?
                     ORDER BY pc.created_at ASC');
              $cstmt->bind_param('i', $pid);
              $cstmt->execute();
              $comments = $cstmt->get_result()->fetch_all(MYSQLI_ASSOC);
              $cstmt->close();
            ?>
            <div class="gc-comment">
              <?php if (empty($comments)): ?>
                <div class="gc-subtle">No comments yet. Be the first to comment.</div>
              <?php else: ?>
                <?php foreach ($comments as $c): ?>
                  <p><span class="by"><?= h($c['full_name']) ?>:</span> <?= nl2br(h($c['comment'])) ?> <span class="gc-subtle" style="margin-left:6px">(<?= h($c['created_at']) ?>)</span></p>
                <?php endforeach; ?>
              <?php endif; ?>

              <!-- Students: text-only comment -->
              <form class="gc-form" method="post">
                <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">
                <input type="hidden" name="action" value="add_comment">
                <input type="hidden" name="post_id" value="<?= $pid ?>">
                <textarea name="comment" placeholder="Add class comment..."></textarea>
                <button class="btn secondary" type="submit">Comment</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div>
        <div class="gc-card">
          <div class="gc-section-title">Upcoming</div>
          <div class="gc-subtle">Due dates will appear here.</div>
        </div>
        <div class="gc-card">
          <div class="gc-section-title">Class code</div>
          <div class="gc-subtle">—</div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($tab === 'classwork'): ?>
    <div class="gc-grid">
      <?php if (empty($classwork)): ?>
        <div class="gc-card gc-subtle">No classwork assigned yet.</div>
      <?php endif; ?>

      <?php foreach ($classwork as $cw): ?>
        <?php
          $cw_id = (int)$cw['id'];
          $sst = $conn->prepare('SELECT status, grade_points, submitted_at, file_url FROM submissions WHERE classwork_id=? AND student_id=?');
          $sst->bind_param('ii', $cw_id, $student_id);
          $sst->execute();
          $sub = $sst->get_result()->fetch_assoc();
          $sst->close();

          $status = $sub['status'] ?? 'missing';
          $submitted_at = $sub['submitted_at'] ?? null;
          $grade = $sub['grade_points'] ?? null;
        ?>
        <div class="gc-card">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:12px">
            <div>
              <div class="gc-section-title">
                <?= h($cw['title']) ?>
                <?= $cw['work_type']==='quiz' ? '📝' : ($cw['work_type']==='material' ? '📎' : '📘') ?>
              </div>
              <?php if(!empty($cw['description'])): ?>
                <div class="gc-post-body" style="margin:4px 0 0"><?= nl2br(h($cw['description'])) ?></div>
              <?php endif; ?>
              <div class="gc-subtle" style="margin-top:6px">
                <?php if(!empty($cw['due_at'])): ?>Due: <?= h($cw['due_at']) ?> · <?php endif; ?>
                <?php if($cw['max_points']!==null): ?>Points: <?= (int)$cw['max_points'] ?><?php endif; ?>
              </div>
              <?php if(!empty($cw['attachment_url'])): ?>
                <div class="gc-attach" style="margin-top:6px"><a target="_blank" href="<?= h($cw['attachment_url']) ?>">View attachment</a></div>
              <?php endif; ?>
            </div>
            <div>
              <span class="gc-status <?= h($status) ?>"><?= ucfirst($status) ?></span>
              <?php if($grade !== null): ?>
                <div class="gc-subtle" style="text-align:right;margin-top:6px">Grade: <?= h($grade) ?><?= $cw['max_points']!==null?'/'.(int)$cw['max_points']:'' ?></div>
              <?php endif; ?>
            </div>
          </div>

          <?php if ($cw['work_type'] === 'assignment'): ?>
            <!-- Student can turn in assignment (this is not Stream material upload) -->
            <form class="gc-form" method="post" enctype="multipart/form-data" style="margin-top:10px">
              <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">
              <input type="hidden" name="action" value="submit_assignment">
              <input type="hidden" name="classwork_id" value="<?= $cw_id ?>">
              <textarea name="text_answer" placeholder="Add your work (text or notes)..."></textarea>
              <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
                <input type="file" name="file_url" accept="*/*">
                <button class="btn" type="submit">Turn in</button>
              </div>
            </form>
            <?php if (!empty($submitted_at)): ?>
              <div class="gc-subtle" style="margin-top:6px">
                Submitted at: <?= h($submitted_at) ?>
                <?php if(!empty($sub['file_url'])): ?> · <a target="_blank" href="<?= h($sub['file_url']) ?>">Your file</a><?php endif; ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($tab === 'people'): ?>
    <div class="gc-two">
      <div class="gc-card">
        <div class="gc-section-title">Teacher</div>
        <p><?= h($people['teacher']) ?></p>
      </div>
      <div class="gc-card">
        <div class="gc-section-title">Classmates (<?= count($people['students']) ?>)</div>
        <ul class="gc-list">
          <?php foreach ($people['students'] as $s): ?>
            <li><?= h($s['full_name']) ?> <span class="gc-subtle">(@<?= h($s['username']) ?>)</span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
