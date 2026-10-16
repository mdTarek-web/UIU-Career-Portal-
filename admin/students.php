<?php
/**
 * UIU Career Portal - Admin: Enrolled Students Directory & Placement Tracking
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin']);
$app_root = get_app_root();
$active_role_section = 'admin';
$page_title = 'Students Directory | UIU Career Portal';

$pdo = get_db_connection();
$students = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT s.*, u.email,
                   (SELECT COUNT(*) FROM applications WHERE student_id = s.id) as applications_count,
                   (SELECT status FROM applications WHERE student_id = s.id ORDER BY id DESC LIMIT 1) as latest_status
            FROM students s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.id ASC
        ");
        $students = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in admin/students.php: " . $e->getMessage());
    }
}

// Fallback demo students
if (empty($students)) {
    $students = [
        ['id' => 1, 'full_name' => 'Mugdha Rahman', 'student_id' => '011201049', 'department' => 'CSE', 'batch' => 'CSE-49', 'cgpa' => 3.75, 'email' => 'mugdha.rahman@uiu.edu.bd', 'applications_count' => 3, 'latest_status' => 'Shortlisted'],
        ['id' => 2, 'full_name' => 'Tahsin Ahmed', 'student_id' => '011201088', 'department' => 'CSE', 'batch' => 'CSE-48', 'cgpa' => 3.82, 'email' => 'tahsin.ahmed@uiu.edu.bd', 'applications_count' => 2, 'latest_status' => 'Selected']
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
        <li><a href="<?= $app_root ?>/admin/jobs.php" class="sidebar-link">💼 Job Moderation</a></li>
        <li><a href="<?= $app_root ?>/admin/students.php" class="sidebar-link active">🎓 Student Directory</a></li>
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
        <h1 class="topbar-title">Student Directory & Academic Placement Profiles</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Verified undergraduate and alumni profiles registered for campus recruitment.</p>
      </div>
    </div>

    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Enrolled Students (<?= count($students) ?>)</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Review GPA distribution and active recruitment status</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Student ID</th>
              <th>Department</th>
              <th>Batch</th>
              <th>CGPA</th>
              <th>Email</th>
              <th>Applications</th>
              <th>Placement Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $s): ?>
              <tr>
                <td style="font-weight: 800; color: var(--slate-900);">
                  <?= htmlspecialchars($s['full_name']) ?>
                </td>
                <td style="font-weight: 700; color: var(--slate-700);"><?= htmlspecialchars($s['student_id']) ?></td>
                <td><span class="badge badge-gray"><?= htmlspecialchars($s['department']) ?></span></td>
                <td><?= htmlspecialchars($s['batch']) ?></td>
                <td style="font-weight: 800; color: var(--primary);"><?= htmlspecialchars((string)$s['cgpa']) ?></td>
                <td style="font-size: 0.8125rem; color: var(--slate-600);"><?= htmlspecialchars($s['email']) ?></td>
                <td><span class="badge badge-blue"><?= $s['applications_count'] ?? 0 ?> applied</span></td>
                <td>
                  <?php if (($s['latest_status'] ?? '') === 'Selected'): ?>
                    <span class="badge badge-green">★ Placed</span>
                  <?php elseif (($s['latest_status'] ?? '') === 'Shortlisted'): ?>
                    <span class="badge badge-blue">✓ Shortlisted</span>
                  <?php else: ?>
                    <span class="badge badge-orange"><?= htmlspecialchars($s['latest_status'] ?? 'Active') ?></span>
                  <?php endif; ?>
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
