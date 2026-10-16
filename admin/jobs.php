<?php
/**
 * UIU Career Portal - Admin: Job & Internship Postings Moderation
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin']);
$app_root = get_app_root();
$active_role_section = 'admin';
$page_title = 'Job Moderation | UIU Career Portal';

$pdo = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $jId = (int)($_POST['job_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($jId > 0) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE jobs SET status = 'Approved' WHERE id = ?")->execute([$jId]);
            $message = "Position #{$jId} approved and now live on student portal!";
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE jobs SET status = 'Rejected' WHERE id = ?")->execute([$jId]);
            $message = "Position #{$jId} rejected.";
        }
    }
}

$jobs = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT j.*, c.name as company_name 
            FROM jobs j 
            JOIN companies c ON j.company_id = c.id 
            ORDER BY j.status = 'Pending' DESC, j.id DESC
        ");
        $jobs = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in admin/jobs.php: " . $e->getMessage());
    }
}

// Fallback demo jobs
if (empty($jobs)) {
    $jobs = [
        ['id' => 1, 'title' => 'Software Engineering Intern', 'company_name' => 'TechFlow Solutions', 'department' => 'CSE', 'type' => 'Internship', 'salary' => 'BDT 20,000 /mo', 'status' => 'Approved', 'deadline' => '2026-12-31'],
        ['id' => 2, 'title' => 'Associate Software Engineer (Full Stack)', 'company_name' => 'Brain Station 23', 'department' => 'CSE', 'type' => 'Full-time', 'salary' => 'BDT 55,000 - 75,000 /mo', 'status' => 'Approved', 'deadline' => '2026-11-30'],
        ['id' => 9, 'title' => 'Machine Learning Engineer', 'company_name' => 'DataNex AI Technologies', 'department' => 'CSE', 'type' => 'Full-time', 'salary' => 'BDT 70,000 /mo', 'status' => 'Pending', 'deadline' => '2026-11-15']
    ];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="portal-layout">
  <aside class="portal-sidebar">
    <div>
      <div class="sidebar-brand">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Administration</div>
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;">UIU Career Office</div>
        <div style="font-size: 0.75rem; color: #94a3b8;">CDIP Directorate</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/admin/dashboard.php" class="sidebar-link">📊 Admin Dashboard</a></li>
        <li><a href="<?= $app_root ?>/admin/companies.php" class="sidebar-link">🏢 Company Directory & Approvals</a></li>
        <li><a href="<?= $app_root ?>/admin/jobs.php" class="sidebar-link active">💼 Job Moderation</a></li>
        <li><a href="<?= $app_root ?>/admin/students.php" class="sidebar-link">🎓 Student Directory</a></li>
        <li><a href="<?= $app_root ?>/admin/reports.php" class="sidebar-link">📈 Placement Statistics & PDF</a></li>
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
        <h1 class="topbar-title">Job & Internship Postings Moderation</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Moderate job openings submitted by corporate recruiters before student visibility.</p>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">All Job Postings (<?= count($jobs) ?>)</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Review job titles, departments, salaries, and moderation status</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Job Title</th>
              <th>Company Name</th>
              <th>Department</th>
              <th>Type</th>
              <th>Salary / Stipend</th>
              <th>Status</th>
              <th>Moderation Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($jobs as $j): ?>
              <tr>
                <td style="font-weight: 800; color: var(--slate-900);">
                  <?= htmlspecialchars($j['title']) ?>
                </td>
                <td style="font-weight: 700; color: var(--primary);">
                  <?= htmlspecialchars($j['company_name']) ?>
                </td>
                <td><span class="badge badge-gray"><?= htmlspecialchars($j['department']) ?></span></td>
                <td><span class="badge badge-gray"><?= htmlspecialchars($j['type']) ?></span></td>
                <td style="font-weight: 800;"><?= htmlspecialchars($j['salary']) ?></td>
                <td>
                  <?php if ($j['status'] === 'Approved'): ?>
                    <span class="badge badge-green">✓ Approved</span>
                  <?php elseif ($j['status'] === 'Pending'): ?>
                    <span class="badge badge-orange">Pending Moderation</span>
                  <?php else: ?>
                    <span class="badge badge-red"><?= htmlspecialchars($j['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="POST" action="<?= $app_root ?>/admin/jobs.php" style="display: inline-flex; gap: 0.35rem;">
                    <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                    <?php if ($j['status'] !== 'Approved'): ?>
                      <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                    <?php endif; ?>
                    <?php if ($j['status'] !== 'Rejected'): ?>
                      <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject this job?')">Reject</button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
