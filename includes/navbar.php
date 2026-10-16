<?php
/**
 * UIU Career Portal - Shared Top Role Switcher & Main Navbar
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$app_root = get_app_root();
$user = current_user();
$current_role = $user['role'] ?? 'guest';
$active_role_section = $active_role_section ?? 'landing';
?>
<!-- 1. TOP BLACK ROLE SWITCHER BAR -->
<aside class="top-role-bar" aria-label="Role Navigation Bar">
  <div class="top-role-container">
    <div class="role-brand-label">
      <span class="pulse-dot"></span>
      <span>UIU Portal Roles:</span>
      <span style="color: #f97316; font-size: 0.75rem; text-transform: capitalize;">
        <?= htmlspecialchars($active_role_section) ?> View
      </span>
    </div>
    <div class="role-nav-tabs">
      <a href="<?= $app_root ?>/index.php" class="role-tab-btn <?= ($active_role_section === 'landing') ? 'active' : '' ?>">
        <span>🌐 Landing</span>
      </a>
      <a href="<?= $app_root ?>/student/dashboard.php" class="role-tab-btn <?= ($active_role_section === 'student') ? 'active' : '' ?>">
        <span>🎓 Student</span>
      </a>
      <a href="<?= $app_root ?>/company/dashboard.php" class="role-tab-btn <?= ($active_role_section === 'company') ? 'active' : '' ?>">
        <span>🏢 Employer</span>
      </a>
      <a href="<?= $app_root ?>/admin/dashboard.php" class="role-tab-btn <?= ($active_role_section === 'admin') ? 'active' : '' ?>">
        <span>🛡️ Admin</span>
      </a>
    </div>
  </div>
</aside>

<!-- 2. UNIFIED MAIN NAVBAR -->
<header class="main-navbar">
  <div class="navbar-container">
    <!-- Brand Logo -->
    <a href="<?= $app_root ?>/index.php#home" class="nav-brand" title="UIU Career Portal Homepage">
      <div class="brand-badge">UIU</div>
      <div>
        <div class="brand-title">UIU CAREER</div>
        <div class="brand-sub">PORTAL <?= $current_role !== 'guest' ? '• ' . strtoupper($current_role) : '' ?></div>
      </div>
    </a>

    <!-- Main Navigation Links -->
    <nav class="nav-links">
      <a href="<?= $app_root ?>/index.php#home" class="nav-link">Home</a>
      <!-- Clicking Jobs navigates to Home Jobs Section -->
      <a href="<?= $app_root ?>/index.php#jobs" class="nav-link">Jobs</a>
      <!-- Clicking Companies navigates to Home Companies Section -->
      <a href="<?= $app_root ?>/index.php#companies" class="nav-link">Companies</a>
      <a href="<?= $app_root ?>/index.php#about" class="nav-link">About</a>
      <a href="<?= $app_root ?>/index.php#contact" class="nav-link">Contact</a>

      <?php if (is_logged_in()): ?>
        <?php if ($current_role === 'student'): ?>
          <a href="<?= $app_root ?>/student/dashboard.php" class="nav-link" style="color: var(--primary); font-weight: 800;">Student Workspace</a>
        <?php elseif ($current_role === 'company'): ?>
          <a href="<?= $app_root ?>/company/dashboard.php" class="nav-link" style="color: var(--primary); font-weight: 800;">Employer Hub</a>
        <?php elseif ($current_role === 'admin'): ?>
          <a href="<?= $app_root ?>/admin/dashboard.php" class="nav-link" style="color: var(--primary); font-weight: 800;">Admin Central</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>

    <!-- Right Controls -->
    <div class="nav-actions">
      <?php if (!is_logged_in()): ?>
        <a href="<?= $app_root ?>/login.php" class="btn btn-outline">Login</a>
        <a href="<?= $app_root ?>/register.php" class="btn btn-primary">Register</a>
      <?php else: ?>
        <span class="badge badge-orange" style="font-size: 0.8125rem;">
          <?= htmlspecialchars($user['name'] ?? $user['email']) ?>
        </span>
        <a href="<?= $app_root ?>/logout.php" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">Logout</a>
      <?php endif; ?>
    </div>
  </div>
</header>
