<?php
/**
 * UIU Career Portal - Student: Main Dashboard
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'Student Dashboard | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$student = null;
$applications = [];
$recommended_jobs = [];
$stats = [
    'applied' => 0,
    'shortlisted' => 0,
    'selected' => 0,
    'interviews' => 0
];

if ($pdo) {
    // 1. Get Student Profile
    $sStmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
    $sStmt->execute([$user['id'] ?? 2]);
    $student = $sStmt->fetch();

    if ($student) {
        $student_id = $student['id'];

        // 2. Fetch Applications with Job & Company metadata
        $aStmt = $pdo->prepare("
            SELECT a.*, j.title as job_title, j.type as job_type, j.salary, j.location, c.name as company_name 
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            WHERE a.student_id = ?
            ORDER BY a.applied_date DESC
        ");
        $aStmt->execute([$student_id]);
        $applications = $aStmt->fetchAll();

        // 3. Compute Stats
        foreach ($applications as $app) {
            $stats['applied']++;
            if ($app['status'] === 'Shortlisted') $stats['shortlisted']++;
            if ($app['status'] === 'Selected' || $app['status'] === 'Offered') $stats['selected']++;
            if (!empty($app['interview_date'])) $stats['interviews']++;
        }

        // 4. Recommended jobs in same department
        $rStmt = $pdo->prepare("
            SELECT j.*, c.name as company_name 
            FROM jobs j 
            JOIN companies c ON j.company_id = c.id 
            WHERE j.department = ? AND j.status = 'Approved' 
            ORDER BY j.featured DESC LIMIT 4
        ");
        $rStmt->execute([$student['department'] ?? 'CSE']);
        $recommended_jobs = $rStmt->fetchAll();
    }
}

// Fallback demo student if db is fresh
if (!$student) {
    $student = [
        'id' => 1,
        'full_name' => 'Mugdha Rahman',
        'student_id' => '011201049',
        'department' => 'CSE',
        'batch' => 'CSE-49',
        'cgpa' => 3.75,
        'skills' => 'JavaScript, React, Node.js, PHP, MySQL, Python'
    ];
    $applications = [
        [
            'id' => 1,
            'job_id' => 1,
            'job_title' => 'Software Engineering Intern',
            'company_name' => 'TechFlow Solutions',
            'job_type' => 'Internship',
            'salary' => 'BDT 20,000 /mo',
            'status' => 'Shortlisted',
            'applied_date' => '2026-08-15',
            'interview_date' => 'Next Thursday, 2:00 PM'
        ],
        [
            'id' => 2,
            'job_id' => 2,
            'job_title' => 'Associate Software Engineer (Full Stack)',
            'company_name' => 'Brain Station 23',
            'job_type' => 'Full-time',
            'salary' => 'BDT 55,000 - 75,000 /mo',
            'status' => 'Under Review',
            'applied_date' => '2026-08-18',
            'interview_date' => null
        ]
    ];
    $stats = ['applied' => 2, 'shortlisted' => 1, 'selected' => 1, 'interviews' => 1];
    $recommended_jobs = [
        ['id' => 3, 'title' => 'Junior Cloud & DevOps Engineer', 'company_name' => 'BRAC IT Services', 'type' => 'Full-time', 'salary' => 'BDT 60,000 /mo'],
        ['id' => 5, 'title' => 'Quality Assurance (QA) Intern', 'company_name' => 'Therap Services', 'type' => 'Internship', 'salary' => 'BDT 22,000 /mo']
    ];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="portal-layout">
  <!-- Left Sidebar -->
  <aside class="portal-sidebar">
    <div>
      <div class="sidebar-brand">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Student Portal</div>
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;"><?= htmlspecialchars($student['full_name']) ?></div>
        <div style="font-size: 0.75rem; color: #94a3b8;">ID: <?= htmlspecialchars($student['student_id']) ?> • <?= htmlspecialchars($student['batch']) ?></div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/student/dashboard.php" class="sidebar-link active">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link">💼 Browse Jobs & Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link">📑 My Applications (<?= count($applications) ?>)</a></li>
        <li><a href="<?= $app_root ?>/student/profile.php" class="sidebar-link">👤 Student Profile</a></li>
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

  <!-- Main Content Area -->
  <main class="portal-main">
    <div class="topbar">
      <div>
        <h1 class="topbar-title">Welcome back, <?= htmlspecialchars(explode(' ', $student['full_name'])[0]) ?>! 👋</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Here is a summary of your campus recruitment activities and applications.</p>
      </div>
      <div style="display: flex; gap: 0.75rem;">
        <a href="<?= $app_root ?>/student/resume_builder.php" class="btn btn-outline btn-sm">
          📄 Download Resume PDF
        </a>
        <a href="<?= $app_root ?>/student/jobs.php" class="btn btn-primary btn-sm">
          🔍 Find New Openings
        </a>
      </div>
    </div>

    <!-- 4 Metric KPI Cards -->
    <div class="dashboard-grid">
      <div class="metric-box">
        <div class="metric-val" style="color: var(--primary);"><?= $stats['applied'] ?></div>
        <div class="metric-lbl">Total Applied</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #3b82f6;"><?= $stats['shortlisted'] ?></div>
        <div class="metric-lbl">Shortlisted</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #8b5cf6;"><?= $stats['interviews'] ?></div>
        <div class="metric-lbl">Interviews Scheduled</div>
      </div>
      <div class="metric-box">
        <div class="metric-val" style="color: #10b981;"><?= $stats['selected'] ?></div>
        <div class="metric-lbl">Offers / Selected</div>
      </div>
    </div>

    <!-- Active Applications Table -->
    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Recent Applications</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Live status updates from corporate hiring teams</p>
        </div>
        <a href="<?= $app_root ?>/student/applications.php" class="btn btn-outline btn-sm">View All &rarr;</a>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Role & Company</th>
              <th>Type</th>
              <th>Applied Date</th>
              <th>Current Status</th>
              <th>Interview / Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($applications)): ?>
              <tr>
                <td colspan="5" style="text-align: center; color: var(--slate-500); padding: 2rem;">
                  You have not applied for any positions yet. <a href="<?= $app_root ?>/student/jobs.php" style="color: var(--primary); font-weight: 700;">Browse openings now</a>.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($applications as $app): ?>
                <tr>
                  <td>
                    <div style="font-weight: 800; color: var(--slate-900);"><?= htmlspecialchars($app['job_title']) ?></div>
                    <div style="font-size: 0.8125rem; color: var(--primary); font-weight: 700;"><?= htmlspecialchars($app['company_name']) ?></div>
                  </td>
                  <td><span class="badge badge-gray"><?= htmlspecialchars($app['job_type']) ?></span></td>
                  <td style="color: var(--slate-600);"><?= htmlspecialchars(substr($app['applied_date'], 0, 10)) ?></td>
                  <td>
                    <?php if ($app['status'] === 'Shortlisted'): ?>
                      <span class="badge badge-blue">✓ Shortlisted</span>
                    <?php elseif ($app['status'] === 'Selected' || $app['status'] === 'Offered'): ?>
                      <span class="badge badge-green">★ Selected / Offered</span>
                    <?php elseif ($app['status'] === 'Rejected'): ?>
                      <span class="badge badge-red">Rejected</span>
                    <?php else: ?>
                      <span class="badge badge-orange"><?= htmlspecialchars($app['status']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div style="display: flex; gap: 0.4rem; align-items: center;">
                      <?php if ($app['status'] === 'Shortlisted'): ?>
                        <a href="<?= $app_root ?>/student/chat.php" class="btn btn-primary btn-sm">💬 Chat Recruiter</a>
                      <?php endif; ?>
                      <?php if ($app['status'] === 'Selected' || $app['status'] === 'Offered' || $app['id'] == 1): ?>
                        <a href="<?= $app_root ?>/student/offer_letter.php" class="btn btn-success btn-sm">📜 Offer Letter</a>
                      <?php endif; ?>
                      <a href="<?= $app_root ?>/student/job_details.php?id=<?= $app['job_id'] ?>" class="btn btn-outline btn-sm">Details</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recommended Openings Card -->
    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Recommended for <?= htmlspecialchars($student['department']) ?> Students</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Openings matching your degree specialization</p>
        </div>
        <a href="<?= $app_root ?>/student/jobs.php" class="btn btn-outline btn-sm">Explore All &rarr;</a>
      </div>
      <div style="padding: 1.25rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
        <?php foreach ($recommended_jobs as $rj): ?>
          <div style="background: #f8fafc; border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span class="badge badge-orange"><?= htmlspecialchars($rj['type']) ?></span>
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--slate-500);"><?= htmlspecialchars($rj['salary']) ?></span>
              </div>
              <h4 style="font-weight: 800; font-size: 1rem; color: var(--slate-900); margin-bottom: 0.25rem;"><?= htmlspecialchars($rj['title']) ?></h4>
              <p style="font-size: 0.8125rem; color: var(--primary); font-weight: 700; margin-bottom: 0.75rem;"><?= htmlspecialchars($rj['company_name']) ?></p>
            </div>
            <a href="<?= $app_root ?>/student/job_details.php?id=<?= $rj['id'] ?>" class="btn btn-primary btn-sm" style="margin-top: 0.75rem;">
              View & Apply &rarr;
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
