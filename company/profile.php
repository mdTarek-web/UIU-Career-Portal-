<?php
/**
 * UIU Career Portal - Company: Profile Management
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['company', 'admin']);
$app_root = get_app_root();
$active_role_section = 'company';
$page_title = 'Company Profile | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$company = null;
$message = '';
$error = '';

if ($pdo) {
    $cStmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ? OR id = ? LIMIT 1");
    $cStmt->execute([$user['id'] ?? 4, $user['profile']['id'] ?? 1]);
    $company = $cStmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = sanitize($_POST['name'] ?? '');
        $sector = sanitize($_POST['sector'] ?? '');
        $industry = sanitize($_POST['industry'] ?? '');
        $location = sanitize($_POST['location'] ?? '');
        $website = sanitize($_POST['website'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $description = sanitize($_POST['description'] ?? '');

        if ($company) {
            $uStmt = $pdo->prepare("
                UPDATE companies 
                SET name = ?, sector = ?, industry = ?, location = ?, website = ?, phone = ?, description = ?
                WHERE id = ?
            ");
            $uStmt->execute([$name, $sector, $industry, $location, $website, $phone, $description, $company['id']]);
            $message = 'Company profile updated successfully!';

            // Refresh
            $cStmt->execute([$user['id'] ?? 4, $user['profile']['id'] ?? 1]);
            $company = $cStmt->fetch();
        }
    }
}

// Fallback demo company
if (!$company) {
    $company = [
        'name' => 'TechFlow Solutions',
        'sector' => 'Software & IT',
        'industry' => 'Enterprise Cloud & Web Systems',
        'location' => 'Plot 24, Road 11, Banani, Dhaka-1213',
        'website' => 'https://techflow.com.bd',
        'email' => 'hr@techflow.com.bd',
        'phone' => '+880 2 9876543',
        'description' => 'Leading software technology consultancy building enterprise solutions for fintech and telecom across Southeast Asia.',
        'status' => 'Verified'
    ];
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
        <div style="font-size: 0.75rem; color: #94a3b8;">Company Profile</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/company/dashboard.php" class="sidebar-link">📊 Employer Hub</a></li>
        <li><a href="<?= $app_root ?>/company/create_job.php" class="sidebar-link">➕ Post New Job / Internship</a></li>
        <li><a href="<?= $app_root ?>/company/applicants.php" class="sidebar-link">👥 Applicants & Shortlisting</a></li>
        <li><a href="<?= $app_root ?>/company/chat.php" class="sidebar-link">💬 Candidate Messages</a></li>
        <li><a href="<?= $app_root ?>/company/profile.php" class="sidebar-link active">🏢 Company Profile</a></li>
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
        <h1 class="topbar-title">Company Profile & Branding</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Information visible to students browsing the UIU career directory.</p>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div style="background: #ffffff; border-radius: var(--radius-xl); border: 1px solid var(--slate-200); padding: 2.25rem; box-shadow: var(--shadow-sm); max-width: 850px;">
      <form method="POST" action="<?= $app_root ?>/company/profile.php">
        
        <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--slate-200);">
          <div style="width: 72px; height: 72px; border-radius: var(--radius-md); background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
            🏢
          </div>
          <div>
            <h2 style="font-size: 1.35rem; font-weight: 900; color: var(--slate-900);"><?= htmlspecialchars($company['name']) ?></h2>
            <div style="font-size: 0.875rem; color: var(--slate-500); font-weight: 700;">
              <?= htmlspecialchars($company['sector']) ?> • <?= htmlspecialchars($company['location']) ?>
            </div>
            <span class="badge badge-green" style="margin-top: 0.35rem;">✓ Verified Campus Partner</span>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Company / Organization Legal Name *</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($company['name']) ?>" required>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Sector *</label>
            <input type="text" name="sector" class="form-control" value="<?= htmlspecialchars($company['sector']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Industry Sub-domain *</label>
            <input type="text" name="industry" class="form-control" value="<?= htmlspecialchars($company['industry'] ?? 'Software & IT') ?>" required>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Headquarters / Office Location *</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($company['location']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Official Website URL</label>
            <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($company['website'] ?? '') ?>" placeholder="https://company.com">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Contact Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($company['phone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Registered Corporate Email (Read-only)</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($company['email'] ?? 'hr@company.com') ?>" readonly style="background-color: var(--slate-100); color: var(--slate-600);">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Company Overview & Engineering Culture</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem; font-size: 1rem;">
          Update Company Profile
        </button>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
