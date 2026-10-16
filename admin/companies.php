<?php
/**
 * UIU Career Portal - Admin: Partner Companies Directory & Moderation
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin']);
$app_root = get_app_root();
$active_role_section = 'admin';
$page_title = 'Company Moderation | UIU Career Portal';

$pdo = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $cId = (int)($_POST['company_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($cId > 0) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE companies SET status = 'Verified', verified = 1 WHERE id = ?")->execute([$cId]);
            $message = "Company #{$cId} verified and granted employer recruitment access!";
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE companies SET status = 'Rejected', verified = 0 WHERE id = ?")->execute([$cId]);
            $message = "Company #{$cId} registration rejected.";
        }
    }
}

$companies = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM companies ORDER BY status = 'Pending' DESC, id DESC");
        $companies = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in admin/companies.php: " . $e->getMessage());
    }
}

// Fallback demo companies
if (empty($companies)) {
    $companies = [
        ['id' => 1, 'name' => 'TechFlow Solutions', 'sector' => 'Software & IT', 'location' => 'Banani, Dhaka', 'website' => 'https://techflow.com.bd', 'status' => 'Verified', 'health_score' => 96],
        ['id' => 2, 'name' => 'Brain Station 23', 'sector' => 'Software & IT', 'location' => 'Mohakhali DOHS, Dhaka', 'website' => 'https://brainstation-23.com', 'status' => 'Verified', 'health_score' => 98],
        ['id' => 3, 'name' => 'BRAC IT Services', 'sector' => 'FinTech', 'location' => 'Gulshan-1, Dhaka', 'website' => 'https://bracits.com', 'status' => 'Verified', 'health_score' => 94],
        ['id' => 8, 'name' => 'DataNex AI Technologies', 'sector' => 'AI & Cloud', 'location' => 'Gulshan-2, Dhaka', 'website' => 'https://datanex.ai', 'status' => 'Pending', 'health_score' => 88]
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
        <li><a href="<?= $app_root ?>/admin/companies.php" class="sidebar-link active">🏢 Company Directory & Approvals</a></li>
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
        <h1 class="topbar-title">Partner Companies Directory & Moderation</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Manage official institutional MoUs and verify employer account requests.</p>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Corporate Partners List (<?= count($companies) ?>)</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Direct employer directory verified by UIU Career Services</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Company Name</th>
              <th>Sector & Domain</th>
              <th>Location</th>
              <th>Website</th>
              <th>Verification Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($companies as $c): ?>
              <tr>
                <td style="font-weight: 800; color: var(--slate-900); font-size: 0.95rem;">
                  <?= htmlspecialchars($c['name']) ?>
                </td>
                <td><?= htmlspecialchars($c['sector']) ?></td>
                <td><?= htmlspecialchars($c['location']) ?></td>
                <td><a href="<?= htmlspecialchars($c['website'] ?? '#') ?>" target="_blank" style="color: var(--primary); font-weight: 700;">Visit ↗</a></td>
                <td>
                  <?php if ($c['status'] === 'Verified'): ?>
                    <span class="badge badge-green">✓ Verified Partner</span>
                  <?php elseif ($c['status'] === 'Pending'): ?>
                    <span class="badge badge-orange">Pending Review</span>
                  <?php else: ?>
                    <span class="badge badge-red"><?= htmlspecialchars($c['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="POST" action="<?= $app_root ?>/admin/companies.php" style="display: inline-flex; gap: 0.35rem;">
                    <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
                    <?php if ($c['status'] !== 'Verified'): ?>
                      <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                    <?php endif; ?>
                    <?php if ($c['status'] !== 'Rejected'): ?>
                      <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject this company?')">Reject</button>
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
