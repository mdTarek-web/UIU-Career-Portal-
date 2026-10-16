<?php
/**
 * UIU Career Portal - Company: Employer Hub Dashboard
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['company', 'admin']);
$app_root = get_app_root();
$active_role_section = 'company';
$page_title = 'Employer Hub | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$company = null;
$posted_jobs = [];
$recent_applicants = [];
$stats = [
    'active_jobs' => 0,
    'total_applicants' => 0,
    'shortlisted' => 0,
    'selected' => 0
];

if ($pdo) {
    try {
        // 1. Get company record
        $cStmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ? OR id = ?");
        $cStmt->execute([$user['id'] ?? 4, $user['profile']['id'] ?? 1]);
        $company = $cStmt->fetch();

        if ($company) {
            $company_id = $company['id'];

            // 2. Fetch company jobs
            $jStmt = $pdo->prepare("SELECT * FROM jobs WHERE company_id = ? ORDER BY id DESC");
            $jStmt->execute([$company_id]);
            $posted_jobs = $jStmt->fetchAll();
            $stats['active_jobs'] = count($posted_jobs);

            // 3. Fetch applications for these jobs
            $aStmt = $pdo->prepare("
                SELECT a.*, s.full_name as student_name, s.student_id as student_uni_id, s.department as student_dept, s.cgpa,
                       j.title as job_title, j.type as job_type 
                FROM applications a
                JOIN jobs j ON a.job_id = j.id
                JOIN students s ON a.student_id = s.id
                WHERE j.company_id = ?
                ORDER BY a.applied_date DESC
            ");
            $aStmt->execute([$company_id]);
            $recent_applicants = $aStmt->fetchAll();
            $stats['total_applicants'] = count($recent_applicants);

            foreach ($recent_applicants as $app) {
                if ($app['status'] === 'Shortlisted') $stats['shortlisted']++;
                if ($app['status'] === 'Selected' || $app['status'] === 'Offered') $stats['selected']++;
            }
        }
    } catch (Exception $e) {
        error_log("Error in company/dashboard.php: " . $e->getMessage());
    }
}

// Fallback demo company
if (!$company) {
    $company = [
        'id' => 1,
        'name' => 'TechFlow Solutions',
        'sector' => 'Software & IT',
        'location' => 'Banani, Dhaka',
        'status' => 'Verified',
        'health_score' => 96
    ];
    $posted_jobs = [
        ['id' => 1, 'title' => 'Software Engineering Intern', 'department' => 'CSE', 'type' => 'Internship', 'salary' => 'BDT 20,000 /mo', 'applicants_count' => 18, 'status' => 'Approved', 'deadline' => '2026-12-31']
    ];
    $recent_applicants = [
        ['id' => 1, 'student_name' => 'Mugdha Rahman', 'student_uni_id' => '011201049', 'student_dept' => 'CSE', 'cgpa' => 3.75, 'job_title' => 'Software Engineering Intern', 'status' => 'Shortlisted', 'applied_date' => '2026-08-15']
    ];
    $stats = ['active_jobs' => 1, 'total_applicants' => 18, 'shortlisted' => 4, 'selected' => 1];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="portal-layout">
  <aside class="portal-sidebar">
    <div>
      <div class="sidebar-brand">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Employer Portal</div>
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;"><?= htmlspecialchars($company['name']) ?></div>
        <div style="font-size: 0.75rem; color: #94a3b8;"><?= htmlspecialchars($company['sector'] ?? 'Corporate Partner') ?></div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/company/dashboard.php" class="sidebar-link active">📊 Employer Hub</a></li>
        <li><a href="<?= $app_root ?>/company/create_job.php" class="sidebar-link">➕ Post New Job / Internship</a></li>
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
        <h1 class="topbar-title">Employer Recruitment Hub</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Manage active vacancies, evaluate talent submissions, and issue offers.</p>
      </div>
      <div style="display: flex; gap: 0.75rem;">
        <a href="<?= $app_root ?>/company/create_job.php" class="btn btn-primary btn-sm">
          ➕ Post New Vacancy
        </a>
      </div>
    </div>

    <!-- 4 KPI Metrics -->
    <div class="dashboard-grid">
      <div class="metric-box">
        <div class="metric-val" style="color: var(--primary);"><?= $stats['active_jobs'] ?></div>
        <div class="metric-lbl">Active Openings</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #3b82f6;"><?= $stats['total_applicants'] ?></div>
        <div class="metric-lbl">Student Applicants</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #8b5cf6;"><?= $stats['shortlisted'] ?></div>
        <div class="metric-lbl">Shortlisted for Rounds</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #10b981;"><?= $stats['selected'] ?></div>
        <div class="metric-lbl">Offers Selected</div>
      </div>
    </div>

    <!-- Active Openings Table -->
    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Your Job & Internship Postings</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Positions actively published on the UIU student portal</p>
        </div>
        <a href="<?= $app_root ?>/company/create_job.php" class="btn btn-outline btn-sm">+ Post Job</a>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Title & Department</th>
              <th>Type</th>
              <th>Remuneration</th>
              <th>Status</th>
              <th>Deadline</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($posted_jobs as $pj): ?>
              <tr>
                <td>
                  <div style="font-weight: 800; color: var(--slate-900);"><?= htmlspecialchars($pj['title']) ?></div>
                  <div style="font-size: 0.8125rem; color: var(--slate-500);"><?= htmlspecialchars($pj['department']) ?></div>
                </td>
                <td><span class="badge badge-gray"><?= htmlspecialchars($pj['type']) ?></span></td>
                <td style="font-weight: 800; color: var(--slate-900);"><?= htmlspecialchars($pj['salary']) ?></td>
                <td><span class="badge badge-green"><?= htmlspecialchars($pj['status']) ?></span></td>
                <td style="color: var(--slate-600);"><?= htmlspecialchars($pj['deadline']) ?></td>
                <td>
                  <div style="display: flex; gap: 0.35rem;">
                    <a href="<?= $app_root ?>/company/edit_job.php?id=<?= $pj['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                    <a href="<?= $app_root ?>/company/applicants.php?job_id=<?= $pj['id'] ?>" class="btn btn-primary btn-sm">Review Applicants</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Applicants -->
    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Recent Applicants</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Review undergraduate profiles and portfolios</p>
        </div>
        <a href="<?= $app_root ?>/company/applicants.php" class="btn btn-outline btn-sm">Review All &rarr;</a>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Department & Batch</th>
              <th>CGPA</th>
              <th>Applied Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($recent_applicants, 0, 5) as $app): ?>
              <tr>
                <td style="font-weight: 800; color: var(--slate-900);"><?= htmlspecialchars($app['student_name']) ?></td>
                <td><?= htmlspecialchars($app['student_dept']) ?> • <?= htmlspecialchars($app['student_uni_id']) ?></td>
                <td style="font-weight: 800; color: var(--primary);"><?= htmlspecialchars((string)$app['cgpa']) ?></td>
                <td><?= htmlspecialchars($app['job_title']) ?></td>
                <td>
                  <span class="badge <?= ($app['status'] === 'Shortlisted') ? 'badge-blue' : 'badge-orange' ?>">
                    <?= htmlspecialchars($app['status']) ?>
                  </span>
                </td>
                <td>
                  <div style="display: flex; gap: 0.35rem;">
                    <a href="<?= $app_root ?>/company/applicants.php" class="btn btn-outline btn-sm">Evaluate</a>
                    <a href="<?= $app_root ?>/company/chat.php" class="btn btn-primary btn-sm">Chat</a>
                  </div>
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
