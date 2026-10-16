<?php
/**
 * UIU Career Portal - Student: Profile Management
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'Student Profile | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$student = null;
$message = '';
$error = '';

if ($pdo) {
    $sStmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
    $sStmt->execute([$user['id'] ?? 2]);
    $student = $sStmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? 'CSE');
        $batch = trim($_POST['batch'] ?? 'CSE-49');
        $cgpa = floatval($_POST['cgpa'] ?? 3.75);
        $linkedin = trim($_POST['linkedin'] ?? '');
        $github = trim($_POST['github'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $skills = trim($_POST['skills'] ?? '');

        if ($student) {
            $uStmt = $pdo->prepare("
                UPDATE students 
                SET full_name = ?, phone = ?, department = ?, batch = ?, cgpa = ?, linkedin = ?, github = ?, address = ?, skills = ?
                WHERE id = ?
            ");
            $uStmt->execute([$fullName, $phone, $department, $batch, $cgpa, $linkedin, $github, $address, $skills, $student['id']]);
            $message = 'Profile updated successfully!';
            
            // Refresh
            $sStmt->execute([$user['id'] ?? 2]);
            $student = $sStmt->fetch();
        }
    }
}

// Fallback demo student
if (!$student) {
    $student = [
        'full_name' => 'Mugdha Rahman',
        'student_id' => '011201049',
        'department' => 'CSE',
        'batch' => 'CSE-49',
        'cgpa' => 3.75,
        'phone' => '+880 1712-345678',
        'address' => 'United City, Badda, Dhaka 1212',
        'linkedin' => 'https://linkedin.com/in/mugdharahman',
        'github' => 'https://github.com/mugdharahman',
        'skills' => 'JavaScript, React, Node.js, PHP, MySQL, Python, TailwindCSS, Git'
    ];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="portal-layout">
  <aside class="portal-sidebar">
    <div>
      <div class="sidebar-brand">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Student Portal</div>
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;"><?= htmlspecialchars($student['full_name']) ?></div>
        <div style="font-size: 0.75rem; color: #94a3b8;">ID: <?= htmlspecialchars($student['student_id']) ?> • <?= htmlspecialchars($student['batch']) ?></div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/student/dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link">💼 Browse Jobs & Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link">📑 My Applications</a></li>
        <li><a href="<?= $app_root ?>/student/profile.php" class="sidebar-link active">👤 Student Profile</a></li>
        <li><a href="<?= $app_root ?>/student/resume_builder.php" class="sidebar-link">📄 ATS Resume Builder</a></li>
        <li><a href="<?= $app_root ?>/student/chat.php" class="sidebar-link">💬 Recruiter Messages</a></li>
        <li><a href="<?= $app_root ?>/student/offer_letter.php" class="sidebar-link">📜 Offer Letters</a></li>
      </ul>
    </div>
    <div style="padding-top: 1.5rem; border-top: 1px solid #334155;">
      <a href="<?= $app_root ?>/logout.php" class="btn btn-outline btn-sm" style="width: 100%; color: #cbd5e1; border-color: #475569;">
        🚪 Sign Out
      </a>
    </div>
  </aside>

  <main class="portal-main">
    <div class="topbar">
      <div>
        <h1 class="topbar-title">Student Profile & Credentials</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Keep your institutional records and portfolio links updated for recruiters.</p>
      </div>
      <a href="<?= $app_root ?>/student/resume_builder.php" class="btn btn-primary btn-sm">
        📄 Sync with Resume Builder &rarr;
      </a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div style="background: #ffffff; border-radius: var(--radius-xl); border: 1px solid var(--slate-200); padding: 2.25rem; box-shadow: var(--shadow-sm);">
      <form method="POST" action="<?= $app_root ?>/student/profile.php">
        
        <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--slate-200);">
          <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #ea580c, #f97316); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; font-weight: 900;">
            <?= substr($student['full_name'], 0, 1) ?>
          </div>
          <div>
            <h2 style="font-size: 1.35rem; font-weight: 900; color: var(--slate-900);"><?= htmlspecialchars($student['full_name']) ?></h2>
            <div style="font-size: 0.875rem; color: var(--slate-500); font-weight: 700;">
              Student ID: <?= htmlspecialchars($student['student_id']) ?> • <?= htmlspecialchars($student['department']) ?> (<?= htmlspecialchars($student['batch']) ?>)
            </div>
            <span class="badge badge-green" style="margin-top: 0.35rem;">✓ Active Enrollment</span>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($student['full_name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Student ID (Immutable)</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($student['student_id']) ?>" readonly style="background-color: var(--slate-100); color: var(--slate-600);">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Academic Department</label>
            <select name="department" class="form-control">
              <option value="CSE" <?= ($student['department'] === 'CSE') ? 'selected' : '' ?>>Computer Science & Engineering (CSE)</option>
              <option value="EEE" <?= ($student['department'] === 'EEE') ? 'selected' : '' ?>>Electrical & Electronic Engineering (EEE)</option>
              <option value="BBA" <?= ($student['department'] === 'BBA') ? 'selected' : '' ?>>Bachelor of Business Administration (BBA)</option>
              <option value="Data Science" <?= ($student['department'] === 'Data Science') ? 'selected' : '' ?>>Data Science & Analytics</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Current CGPA</label>
            <input type="number" step="0.01" min="0" max="4.00" name="cgpa" class="form-control" value="<?= htmlspecialchars((string)$student['cgpa']) ?>">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Batch</label>
            <input type="text" name="batch" class="form-control" value="<?= htmlspecialchars($student['batch']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Contact Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone'] ?? '') ?>">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">LinkedIn Profile URL</label>
            <input type="url" name="linkedin" class="form-control" value="<?= htmlspecialchars($student['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/username">
          </div>
          <div class="form-group">
            <label class="form-label">GitHub / Portfolio URL</label>
            <input type="url" name="github" class="form-control" value="<?= htmlspecialchars($student['github'] ?? '') ?>" placeholder="https://github.com/username">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Current Address</label>
          <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($student['address'] ?? '') ?>" placeholder="City, Area, Dhaka">
        </div>

        <div class="form-group">
          <label class="form-label">Key Technical Skills & Competencies (Comma-separated)</label>
          <textarea name="skills" class="form-control" rows="3"><?= htmlspecialchars($student['skills'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem; font-size: 1rem;">
          Save Profile Changes
        </button>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
