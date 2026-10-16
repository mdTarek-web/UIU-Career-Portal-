<?php
/**
 * UIU Career Portal - Student: Apply for Job/Internship Endpoint & Confirmation
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'Apply for Position | UIU Career Portal';

$job_id = (int)($_GET['id'] ?? $_POST['job_id'] ?? 1);
$student_id = $_SESSION['user']['profile']['id'] ?? 1;
$pdo = get_db_connection();
$job = null;
$error = '';
$success = '';

if ($pdo && $job_id > 0) {
    $stmt = $pdo->prepare("SELECT j.*, c.name as company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();
}

if (!$job) {
    header("Location: {$app_root}/student/jobs.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_apply'])) {
    $notes = sanitize($_POST['cover_note'] ?? 'Applied via UIU Career Portal.');

    if ($pdo) {
        try {
            // Check for duplicate application
            $dupCheck = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND student_id = ?");
            $dupCheck->execute([$job_id, $student_id]);
            if ($dupCheck->fetch()) {
                $error = "You have already submitted an application for this position.";
            } else {
                $ins = $pdo->prepare("INSERT INTO applications (job_id, student_id, status, notes) VALUES (?, ?, 'Applied', ?)");
                $ins->execute([$job_id, $student_id, $notes]);

                $upd = $pdo->prepare("UPDATE jobs SET applicants_count = applicants_count + 1 WHERE id = ?");
                $upd->execute([$job_id]);

                $success = "Application successfully submitted to {$job['company_name']}! You can monitor updates in your Applications dashboard.";
            }
        } catch (PDOException $e) {
            $error = "Notice: Application could not be processed: " . $e->getMessage();
        }
    } else {
        $success = "Application successfully recorded (demo mode)!";
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="portal-layout">
  <aside class="portal-sidebar">
    <div>
      <div class="sidebar-brand">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Student Portal</div>
        <div style="font-size: 1.125rem; font-weight: 900;">Mugdha Rahman</div>
        <div style="font-size: 0.75rem; color: #ffedd5;">ID: 011201049 • CSE-49</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/student/dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link active">💼 Browse Jobs</a></li>
        <li><a href="<?= $app_root ?>/student/internships.php" class="sidebar-link">🎓 Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link">📄 My Applications</a></li>
        <li><a href="<?= $app_root ?>/student/companies.php" class="sidebar-link">🏢 Partner Companies</a></li>
        <li><a href="<?= $app_root ?>/student/resume.php" class="sidebar-link">📝 Resume Builder</a></li>
        <li><a href="<?= $app_root ?>/student/chat.php" class="sidebar-link">💬 Chat & Messages</a></li>
        <li><a href="<?= $app_root ?>/student/profile.php" class="sidebar-link">👤 My Profile</a></li>
      </ul>
    </div>
    <div>
      <a href="<?= $app_root ?>/logout.php" class="sidebar-link" style="color: #fecaca;">🚪 Log Out</a>
    </div>
  </aside>

  <main class="portal-content">
    <div style="max-width: 750px; margin: 0 auto;">
      <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 900; color: var(--slate-900);">Submit Application</h1>
        <p style="color: var(--slate-500); font-size: 0.875rem;">Confirm candidate profile and attach cover statement for hiring team review.</p>
      </div>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($success) ?><br><br>
          <a href="<?= $app_root ?>/student/applications.php" class="btn btn-primary" style="display: inline-block; padding: 0.4rem 0.875rem; font-size: 0.8125rem; margin-top: 0.5rem;">
            Go to My Applications &rarr;
          </a>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (empty($success)): ?>
        <div class="job-card" style="padding: 2.5rem;">
          <div style="background-color: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
            <span class="badge badge-orange"><?= htmlspecialchars($job['type']) ?></span>
            <h3 style="font-size: 1.25rem; font-weight: 900; margin-top: 0.5rem; color: var(--slate-900);"><?= htmlspecialchars($job['title']) ?></h3>
            <p style="color: var(--primary); font-weight: 700; font-size: 0.875rem;"><?= htmlspecialchars($job['company_name']) ?> • <?= htmlspecialchars($job['salary']) ?></p>
          </div>

          <form method="POST" action="apply.php" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

            <div>
              <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
                Candidate Name & University Standing
              </label>
              <input type="text" readonly value="Mugdha Rahman (ID: 011201049, CGPA: 3.75, B.Sc CSE)" class="search-input" style="padding-left: 1rem; background-color: var(--slate-100);">
            </div>

            <div>
              <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
                ATS Resume on File
              </label>
              <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: var(--radius-md); background: #ffffff;">
                <span style="font-size: 0.875rem; font-weight: 700; color: var(--slate-800);">📄 Mugdha_Rahman_UIU_Resume.pdf</span>
                <a href="<?= $app_root ?>/student/resume.php" style="color: var(--primary); font-size: 0.75rem; font-weight: 700; text-decoration: underline;">View / Update</a>
              </div>
            </div>

            <div>
              <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
                Cover Note / Pitch to Recruiter (Optional)
              </label>
              <textarea name="cover_note" rows="4" class="search-input" style="padding: 1rem; width: 100%; height: auto;" placeholder="Briefly state why your technical skills and project experience make you an ideal match for this opening..."></textarea>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
              <a href="<?= $app_root ?>/student/job_details.php?id=<?= $job['id'] ?>" class="btn btn-secondary">
                Cancel
              </a>
              <button type="submit" name="confirm_apply" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                Confirm & Submit Application
              </button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
