<?php
/**
 * UIU Career Portal - Company: Post New Job / Internship
 * Form handling and persistence directly to MySQL
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['company', 'admin']);
$app_root = get_app_root();
$active_role_section = 'company';
$page_title = 'Post New Job / Internship | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $department = sanitize($_POST['department'] ?? 'CSE');
    $type = sanitize($_POST['type'] ?? 'Full-time');
    $workplace = sanitize($_POST['workplace'] ?? 'Hybrid');
    $location = sanitize($_POST['location'] ?? 'Dhaka, Bangladesh');
    $salary = sanitize($_POST['salary'] ?? '');
    $deadline = sanitize($_POST['deadline'] ?? '');
    $skills = sanitize($_POST['skills'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $requirements = sanitize($_POST['requirements'] ?? '');
    $responsibilities = sanitize($_POST['responsibilities'] ?? '');

    if (empty($title) || empty($salary) || empty($deadline) || empty($description)) {
        $error = 'Please fill out all mandatory fields: Title, Remuneration, Deadline, and Description.';
    } else {
        if ($pdo) {
            try {
                // Fetch company id
                $cStmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ? OR id = ? LIMIT 1");
                $cStmt->execute([$user['id'] ?? 4, $user['profile']['id'] ?? 1]);
                $cRow = $cStmt->fetch();
                $company_id = $cRow ? $cRow['id'] : 1;

                $insStmt = $pdo->prepare("
                    INSERT INTO jobs (company_id, title, department, type, workplace, location, salary, deadline, skills, description, requirements, responsibilities, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Approved')
                ");
                $insStmt->execute([
                    $company_id, $title, $department, $type, $workplace, $location, $salary, $deadline, $skills, $description, $requirements, $responsibilities
                ]);

                $message = "Position '{$title}' successfully published and visible to UIU students!";
            } catch (Exception $e) {
                $error = 'Failed to publish job: ' . $e->getMessage();
            }
        } else {
            $message = "Position '{$title}' recorded in demo mode!";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="portal-layout">
  <aside class="portal-sidebar">
    <div>
      <div class="sidebar-brand">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Employer Portal</div>
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;">Post Vacancy</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/company/dashboard.php" class="sidebar-link">📊 Employer Hub</a></li>
        <li><a href="<?= $app_root ?>/company/create_job.php" class="sidebar-link active">➕ Post New Job / Internship</a></li>
        <li><a href="<?= $app_root ?>/company/applicants.php" class="sidebar-link">👥 Applicants & Shortlisting</a></li>
        <li><a href="<?= $app_root ?>/company/chat.php" class="sidebar-link">💬 Candidate Messages</a></li>
        <li><a href="<?= $app_root ?>/company/profile.php" class="sidebar-link">🏢 Company Profile</a></li>
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
        <h1 class="topbar-title">Post New Job or Internship</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Advertise verified career openings directly to United International University students.</p>
      </div>
      <a href="<?= $app_root ?>/company/dashboard.php" class="btn btn-outline btn-sm">&larr; Back to Hub</a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($message) ?>
        <div style="margin-top: 0.5rem;">
          <a href="<?= $app_root ?>/company/dashboard.php" class="btn btn-primary btn-sm">View in Hub &rarr;</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div style="background: #ffffff; border-radius: var(--radius-xl); border: 1px solid var(--slate-200); padding: 2.25rem; box-shadow: var(--shadow-sm); max-width: 900px;">
      <form method="POST" action="<?= $app_root ?>/company/create_job.php">
        
        <div class="form-group">
          <label class="form-label">Job / Internship Title *</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Associate Software Engineer or Cloud Platform Intern" required>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Target Academic Department *</label>
            <select name="department" class="form-control">
              <option value="CSE">Computer Science & Engineering (CSE)</option>
              <option value="EEE">Electrical & Electronic Engineering (EEE)</option>
              <option value="BBA">Bachelor of Business Administration (BBA)</option>
              <option value="Data Science">Data Science & Analytics</option>
              <option value="Civil">Civil Engineering</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Employment Type *</label>
            <select name="type" class="form-control">
              <option value="Internship">Internship (Undergraduate Placement)</option>
              <option value="Full-time">Full-time (Graduate Employment)</option>
              <option value="Contract">Contract / Project Based</option>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Workplace Model *</label>
            <select name="workplace" class="form-control">
              <option value="Hybrid">Hybrid (On-site & Remote)</option>
              <option value="On-site">On-site (Office based)</option>
              <option value="Remote">Remote</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Office Location *</label>
            <input type="text" name="location" class="form-control" value="Banani, Dhaka" required>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Remuneration / Stipend / Salary *</label>
            <input type="text" name="salary" class="form-control" placeholder="e.g. BDT 25,000 /mo or BDT 60,000 - 80,000 /mo" required>
          </div>
          <div class="form-group">
            <label class="form-label">Application Deadline *</label>
            <input type="date" name="deadline" class="form-control" value="<?= date('Y-m-d', strtotime('+45 days')) ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Key Required Skills (Comma-separated) *</label>
          <input type="text" name="skills" class="form-control" placeholder="e.g. JavaScript, React, Node.js, MySQL, Git, Docker" required>
        </div>

        <div class="form-group">
          <label class="form-label">Job Overview & Description *</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Detail the engineering team, tech stack, and learning opportunities..." required></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Candidate Requirements & Academic Qualifications</label>
          <textarea name="requirements" class="form-control" rows="3" placeholder="e.g. Completed Data Structures, minimum CGPA 3.30, strong algorithmic fundamentals..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Key Responsibilities</label>
          <textarea name="responsibilities" class="form-control" rows="3" placeholder="e.g. Develop microservice APIs, participate in code reviews, write unit tests..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2.25rem; font-size: 1rem;">
          Publish Opening to UIU Portal &rarr;
        </button>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
