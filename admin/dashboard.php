<?php
/**
 * UIU Career Portal - Admin: Career Services Directorate Dashboard
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin']);
$app_root = get_app_root();
$active_role_section = 'admin';
$page_title = 'Career Services Administration | UIU Career Portal';

$pdo = get_db_connection();
$message = '';

// Handle quick company or job approval actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (isset($_POST['action']) && isset($_POST['company_id'])) {
        $cId = (int)$_POST['company_id'];
        $act = $_POST['action'];
        if ($act === 'approve_company') {
            $pdo->prepare("UPDATE companies SET status = 'Verified', verified = 1 WHERE id = ?")->execute([$cId]);
            $message = "Company #{$cId} verified and approved!";
        } elseif ($act === 'reject_company') {
            $pdo->prepare("UPDATE companies SET status = 'Rejected', verified = 0 WHERE id = ?")->execute([$cId]);
            $message = "Company #{$cId} registration rejected.";
        }
    } elseif (isset($_POST['action']) && isset($_POST['job_id'])) {
        $jId = (int)$_POST['job_id'];
        $act = $_POST['action'];
        if ($act === 'approve_job') {
            $pdo->prepare("UPDATE jobs SET status = 'Approved' WHERE id = ?")->execute([$jId]);
            $message = "Job posting #{$jId} approved for publication!";
        } elseif ($act === 'reject_job') {
            $pdo->prepare("UPDATE jobs SET status = 'Rejected' WHERE id = ?")->execute([$jId]);
            $message = "Job posting #{$jId} rejected.";
        }
    }
}

// Compute Metrics
$stats = [
    'total_students' => 0,
    'total_companies' => 0,
    'pending_companies' => 0,
    'total_jobs' => 0,
    'pending_jobs' => 0,
    'placed_students' => 0
];
$pending_companies = [];
$pending_jobs = [];
$recent_placements = [];

if ($pdo) {
    try {
        $stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        $stats['total_companies'] = $pdo->query("SELECT COUNT(*) FROM companies WHERE status = 'Verified'")->fetchColumn();
        $stats['pending_companies'] = $pdo->query("SELECT COUNT(*) FROM companies WHERE status = 'Pending'")->fetchColumn();
        $stats['total_jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'Approved'")->fetchColumn();
        $stats['pending_jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'Pending'")->fetchColumn();
        $stats['placed_students'] = $pdo->query("SELECT COUNT(*) FROM applications WHERE status IN ('Selected', 'Offered')")->fetchColumn();

        // Pending Companies
        $pcStmt = $pdo->query("SELECT * FROM companies WHERE status = 'Pending' ORDER BY id DESC LIMIT 5");
        $pending_companies = $pcStmt->fetchAll();

        // Pending Jobs
        $pjStmt = $pdo->query("SELECT j.*, c.name as company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'Pending' ORDER BY j.id DESC LIMIT 5");
        $pending_jobs = $pjStmt->fetchAll();

        // Recent Placements
        $rpStmt = $pdo->query("
            SELECT a.*, s.full_name as student_name, s.student_id as student_uni_id, s.department as student_dept,
                   j.title as job_title, j.salary, c.name as company_name 
            FROM applications a
            JOIN students s ON a.student_id = s.id
            JOIN jobs j ON a.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            WHERE a.status IN ('Selected', 'Offered', 'Shortlisted')
            ORDER BY a.id DESC LIMIT 6
        ");
        $recent_placements = $rpStmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in admin/dashboard.php: " . $e->getMessage());
    }
}

// Fallback demo data
if ($stats['total_students'] === 0) {
    $stats = [
        'total_students' => 1420,
        'total_companies' => 85,
        'pending_companies' => 2,
        'total_jobs' => 124,
        'pending_jobs' => 3,
        'placed_students' => 842
    ];
    $pending_companies = [
        ['id' => 8, 'name' => 'DataNex AI Technologies', 'sector' => 'Artificial Intelligence & Cloud', 'location' => 'Gulshan-2, Dhaka', 'website' => 'https://datanex.ai', 'created_at' => '2026-10-01']
    ];
    $pending_jobs = [
        ['id' => 9, 'title' => 'Machine Learning Engineer', 'company_name' => 'DataNex AI Technologies', 'department' => 'CSE', 'salary' => 'BDT 70,000 /mo', 'type' => 'Full-time']
    ];
    $recent_placements = [
        ['student_name' => 'Mugdha Rahman', 'student_uni_id' => '011201049', 'student_dept' => 'CSE', 'company_name' => 'TechFlow Solutions', 'job_title' => 'Software Engineering Intern', 'status' => 'Shortlisted', 'salary' => 'BDT 20,000 /mo'],
        ['student_name' => 'Tahsin Ahmed', 'student_uni_id' => '011201088', 'student_dept' => 'CSE', 'company_name' => 'Brain Station 23', 'job_title' => 'Associate Software Engineer', 'status' => 'Selected', 'salary' => 'BDT 65,000 /mo']
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
        <li><a href="<?= $app_root ?>/admin/dashboard.php" class="sidebar-link active">📊 Admin Dashboard</a></li>
        <li><a href="<?= $app_root ?>/admin/companies.php" class="sidebar-link">🏢 Company Directory & Approvals</a></li>
        <li><a href="<?= $app_root ?>/admin/jobs.php" class="sidebar-link">💼 Job Moderation</a></li>
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
        <h1 class="topbar-title">UIU Career Services Administration</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Directorate of Career Services and Alumni Affairs Management Console</p>
      </div>
      <div style="display: flex; gap: 0.75rem;">
        <a href="<?= $app_root ?>/admin/reports.php" class="btn btn-primary btn-sm">
          📊 Generate Placement Report PDF
        </a>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- 4 KPI Metrics -->
    <div class="dashboard-grid">
      <div class="metric-box">
        <div class="metric-val" style="color: var(--primary);"><?= $stats['total_students'] ?></div>
        <div class="metric-lbl">Enrolled Students</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #3b82f6;"><?= $stats['total_companies'] ?></div>
        <div class="metric-lbl">Verified Employers</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #f59e0b;"><?= $stats['pending_companies'] ?></div>
        <div class="metric-lbl">Pending Approvals</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #10b981;"><?= $stats['placed_students'] ?></div>
        <div class="metric-lbl">Students Placed</div>
      </div>
    </div>

    <!-- Pending Company Approvals -->
    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Pending Company Registrations</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Verify legal corporate credentials and approve portal access</p>
        </div>
        <a href="<?= $app_root ?>/admin/companies.php" class="btn btn-outline btn-sm">All Companies &rarr;</a>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Company Name</th>
              <th>Sector & Domain</th>
              <th>Office Location</th>
              <th>Website</th>
              <th>Moderation Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pending_companies)): ?>
              <tr>
                <td colspan="5" style="text-align: center; color: var(--slate-500); padding: 1.5rem;">
                  ✓ No company registrations currently pending approval.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($pending_companies as $pc): ?>
                <tr>
                  <td style="font-weight: 800; color: var(--slate-900);"><?= htmlspecialchars($pc['name']) ?></td>
                  <td><?= htmlspecialchars($pc['sector']) ?></td>
                  <td><?= htmlspecialchars($pc['location']) ?></td>
                  <td><a href="<?= htmlspecialchars($pc['website'] ?? '#') ?>" target="_blank" style="color: var(--primary); font-weight: 700;">Visit &rarr;</a></td>
                  <td>
                    <form method="POST" action="<?= $app_root ?>/admin/dashboard.php" style="display: inline-flex; gap: 0.35rem;">
                      <input type="hidden" name="company_id" value="<?= $pc['id'] ?>">
                      <button type="submit" name="action" value="approve_company" class="btn btn-success btn-sm">✓ Approve</button>
                      <button type="submit" name="action" value="reject_company" class="btn btn-danger btn-sm" onclick="return confirm('Reject this company?')">✕ Reject</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Placement Monitoring Feed -->
    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Recent Placement & Selection Pipeline</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Live hiring outcomes across CSE, EEE, and BBA departments</p>
        </div>
        <a href="<?= $app_root ?>/admin/reports.php" class="btn btn-outline btn-sm">Full Analytics &rarr;</a>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Student Candidate</th>
              <th>Department</th>
              <th>Hiring Company</th>
              <th>Designation</th>
              <th>Status</th>
              <th>Remuneration</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_placements as $rp): ?>
              <tr>
                <td>
                  <div style="font-weight: 800; color: var(--slate-900);"><?= htmlspecialchars($rp['student_name']) ?></div>
                  <div style="font-size: 0.75rem; color: var(--slate-500);">ID: <?= htmlspecialchars($rp['student_uni_id']) ?></div>
                </td>
                <td><span class="badge badge-gray"><?= htmlspecialchars($rp['student_dept']) ?></span></td>
                <td style="font-weight: 800; color: var(--primary);"><?= htmlspecialchars($rp['company_name']) ?></td>
                <td><?= htmlspecialchars($rp['job_title']) ?></td>
                <td>
                  <?php if ($rp['status'] === 'Selected' || $rp['status'] === 'Offered'): ?>
                    <span class="badge badge-green">★ Selected / Placed</span>
                  <?php else: ?>
                    <span class="badge badge-blue">✓ Shortlisted</span>
                  <?php endif; ?>
                </td>
                <td style="font-weight: 800;"><?= htmlspecialchars($rp['salary'] ?? 'Standard') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
