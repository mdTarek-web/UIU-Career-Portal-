<?php
/**
 * UIU Career Portal - Company: Edit Job / Internship Posting
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['company', 'admin']);
$app_root = get_app_root();
$active_role_section = 'company';
$page_title = 'Edit Job Posting | UIU Career Portal';

$job_id = (int)($_GET['id'] ?? 1);
$pdo = get_db_connection();
$job = null;
$msg = '';

if ($pdo && $job_id > 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = sanitize($_POST['title'] ?? '');
        $salary = sanitize($_POST['salary'] ?? '');
        $deadline = sanitize($_POST['deadline'] ?? '');
        $skills = sanitize($_POST['skills'] ?? '');
        $description = sanitize($_POST['description'] ?? '');

        try {
            $upd = $pdo->prepare("UPDATE jobs SET title = ?, salary = ?, deadline = ?, skills = ?, description = ? WHERE id = ?");
            $upd->execute([$title, $salary, $deadline, $skills, $description, $job_id]);
            $msg = "Job details successfully updated and synchronized!";
        } catch (PDOException $e) {
            $msg = "Error updating job details.";
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();
}

if (!$job) {
    $job = [
        'id' => $job_id,
        'title' => 'Software Engineering Intern',
        'department' => 'CSE',
        'type' => 'Internship',
        'salary' => 'BDT 20,000 /mo',
        'deadline' => '2026-12-31',
        'skills' => 'JavaScript, React, Node.js, Git',
        'description' => 'Work on scalable cloud microservices. Great mentorship provided for UIU undergraduates.'
    ];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="portal-layout">
  <aside class="portal-sidebar">
    <div>
      <div class="sidebar-brand">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Recruiter Hub</div>
        <div style="font-size: 1.125rem; font-weight: 900;">TechFlow Solutions</div>
        <div style="font-size: 0.75rem; color: #ffedd5;">Verified Corporate Partner</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/company/dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/company/jobs.php" class="sidebar-link active">💼 Post & Manage Jobs</a></li>
        <li><a href="<?= $app_root ?>/company/internships.php" class="sidebar-link">🎓 Post Internships</a></li>
        <li><a href="<?= $app_root ?>/company/applications.php" class="sidebar-link">📄 Applications Received</a></li>
        <li><a href="<?= $app_root ?>/company/shortlist.php" class="sidebar-link">⭐ Shortlisted Talent</a></li>
        <li><a href="<?= $app_root ?>/company/chat.php" class="sidebar-link">💬 Chat with Students</a></li>
        <li><a href="<?= $app_root ?>/company/profile.php" class="sidebar-link">🏢 Company Profile</a></li>
      </ul>
    </div>
    <div>
      <a href="<?= $app_root ?>/logout.php" class="sidebar-link" style="color: #fecaca;">🚪 Log Out</a>
    </div>
  </aside>

  <main class="portal-content">
    <div style="margin-bottom: 2rem;">
      <a href="<?= $app_root ?>/company/jobs.php" style="font-size: 0.8125rem; font-weight: 700; color: var(--primary);">
        &larr; Back to Active Jobs
      </a>
      <h1 style="font-size: 2rem; font-weight: 900; color: var(--slate-900); margin-top: 0.5rem;">Edit Job Posting #<?= $job['id'] ?></h1>
    </div>

    <?php if (!empty($msg)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="job-card" style="max-width: 800px; padding: 2.5rem;">
      <form method="POST" action="edit_job.php?id=<?= $job['id'] ?>" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div>
          <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
            Job Title
          </label>
          <input type="text" name="title" required value="<?= htmlspecialchars($job['title']) ?>" class="search-input" style="padding-left: 1rem;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
          <div>
            <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
              Salary / Compensation Package
            </label>
            <input type="text" name="salary" required value="<?= htmlspecialchars($job['salary']) ?>" class="search-input" style="padding-left: 1rem;">
          </div>
          <div>
            <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
              Application Deadline
            </label>
            <input type="date" name="deadline" required value="<?= htmlspecialchars($job['deadline']) ?>" class="search-input" style="padding-left: 1rem;">
          </div>
        </div>

        <div>
          <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
            Required Skills (Comma separated)
          </label>
          <input type="text" name="skills" required value="<?= htmlspecialchars($job['skills']) ?>" class="search-input" style="padding-left: 1rem;">
        </div>

        <div>
          <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
            Description
          </label>
          <textarea name="description" rows="5" required class="search-input" style="padding: 1rem; width: 100%; height: auto;"><?= htmlspecialchars($job['description']) ?></textarea>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
          <a href="<?= $app_root ?>/company/jobs.php" class="btn btn-secondary">
            Cancel
          </a>
          <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
            Save Posting Changes
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
