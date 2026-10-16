<?php
/**
 * UIU Career Portal - Guest View: Public Partner Companies Directory
 * Guests can view public verified company listings without having to log in.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$app_root = get_app_root();
$active_role_section = 'landing';
$page_title = 'Partner Companies Directory | UIU Career Portal';

$search = sanitize($_GET['q'] ?? '');
$sector = sanitize($_GET['sector'] ?? 'All');
$companies = get_verified_companies($search, $sector);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="section-wrapper">
  <div class="section-head">
    <div>
      <span class="badge badge-orange" style="margin-bottom: 0.5rem;">Corporate Relations</span>
      <h1 class="section-title">UIU Partner Companies Directory</h1>
      <p class="section-subtitle">
        Explore accredited multinational corporations, software export houses, and financial institutions partnering with United International University.
      </p>
    </div>
    <div style="font-size: 0.875rem; font-weight: 700; color: var(--primary);">
      <?= count($companies) ?> Accredited Partners
    </div>
  </div>

  <!-- Search & Sector Filter -->
  <form method="GET" action="companies.php" class="filter-card">
    <div class="search-input-wrapper">
      <input 
        type="text" 
        name="q" 
        value="<?= htmlspecialchars($search) ?>" 
        placeholder="Search partner companies by name, domain, industry..." 
        class="search-input"
        style="padding-left: 1rem;"
      >
    </div>
    <select name="sector" class="filter-select">
      <option value="All" <?= $sector === 'All' ? 'selected' : '' ?>>All Sectors</option>
      <option value="Software & IT" <?= $sector === 'Software & IT' ? 'selected' : '' ?>>Software & IT</option>
      <option value="Financial Technology" <?= $sector === 'Financial Technology' ? 'selected' : '' ?>>Financial Technology</option>
      <option value="Healthcare Software" <?= $sector === 'Healthcare Software' ? 'selected' : '' ?>>Healthcare Software</option>
      <option value="E-Commerce & Logistics" <?= $sector === 'E-Commerce & Logistics' ? 'selected' : '' ?>>E-Commerce & Logistics</option>
    </select>
    <button type="submit" class="btn btn-primary" style="padding: 0.625rem 1.25rem;">
      Search Directory
    </button>
    <a href="companies.php" class="btn btn-secondary">
      Reset
    </a>
  </form>

  <!-- Companies Cards Grid -->
  <div class="cards-grid">
    <?php foreach ($companies as $comp): ?>
      <article class="job-card" style="border-radius: var(--radius-lg);">
        <div class="card-top">
          <span class="badge badge-green">✓ Verified Corporate MoU</span>
          <span class="badge badge-orange">Health Score: <?= $comp['health_score'] ?? 95 ?>%</span>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--slate-900);">
          <?= htmlspecialchars($comp['name']) ?>
        </h3>
        <p style="font-size: 0.875rem; color: var(--primary); font-weight: 700; margin-top: 0.25rem;">
          <?= htmlspecialchars($comp['sector']) ?> • <span style="color: var(--slate-600);"><?= htmlspecialchars($comp['industry'] ?? '') ?></span>
        </p>
        <p style="font-size: 0.8125rem; color: var(--slate-500); margin: 0.5rem 0;">
          📍 <?= htmlspecialchars($comp['location']) ?>
        </p>
        <p style="font-size: 0.875rem; color: var(--slate-600); line-height: 1.6; margin-top: 0.5rem;">
          <?= htmlspecialchars($comp['description'] ?? 'Partnering with UIU Directorate of Career Services for graduate employment and internship programs.') ?>
        </p>
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--slate-100); display: flex; justify-content: space-between; align-items: center; font-size: 0.8125rem; color: var(--slate-500);">
          <span style="font-weight: 700; color: var(--slate-800);"><?= $comp['open_jobs_count'] ?? 0 ?> Active Openings</span>
          <a href="<?= $app_root ?>/index.php#jobs" class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">
            View Live Roles
          </a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
