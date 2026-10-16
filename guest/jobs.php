<?php
/**
 * UIU Career Portal - Guest View: Public Jobs & Internships
 * Guests can browse verified vacancies, but applying requires student authentication.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$app_root = get_app_root();
$active_role_section = 'landing';
$page_title = 'Public Job Listings | UIU Career Portal';

$pdo = get_db_connection();
$jobs = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT j.*, c.name as company_name, c.logo as company_logo, c.location as company_location 
            FROM jobs j 
            JOIN companies c ON j.company_id = c.id 
            WHERE j.status = 'Approved' 
            ORDER BY j.featured DESC, j.id DESC
        ");
        $jobs = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in guest/jobs.php: " . $e->getMessage());
    }
}

if (empty($jobs)) {
    $jobs = [
        ['id' => 1, 'title' => 'Software Engineering Intern', 'company_name' => 'TechFlow Solutions', 'department' => 'CSE', 'type' => 'Internship', 'salary' => 'BDT 20,000 /mo', 'location' => 'Banani, Dhaka', 'skills' => 'JavaScript, React, Node.js', 'description' => 'Join our core platform engineering team working on scalable cloud microservices.'],
        ['id' => 2, 'title' => 'Associate Software Engineer (Full Stack)', 'company_name' => 'Brain Station 23', 'department' => 'CSE', 'type' => 'Full-time', 'salary' => 'BDT 55,000 - 75,000 /mo', 'location' => 'Mohakhali DOHS, Dhaka', 'skills' => 'Java, Spring Boot, MySQL', 'description' => 'Exciting opening for ambitious graduating students and fresh alumni.'],
        ['id' => 3, 'title' => 'Junior Cloud & DevOps Engineer', 'company_name' => 'BRAC IT Services', 'department' => 'CSE', 'type' => 'Full-time', 'salary' => 'BDT 60,000 /mo', 'location' => 'Gulshan-1, Dhaka', 'skills' => 'Docker, Linux, AWS', 'description' => 'Support high-availability microfinance cloud infrastructure.']
    ];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="section-wrapper">
  <div class="section-head">
    <div>
      <span class="badge badge-orange" style="margin-bottom: 0.5rem;">Public Portal</span>
      <h1 class="section-title">Verified Job & Internship Openings</h1>
      <p class="section-subtitle">
        Explore current employment openings posted by United International University corporate recruitment partners.
      </p>
    </div>
    <div id="jobs-match-count" style="font-size: 0.875rem; font-weight: 800; color: var(--primary);">
      Showing <?= count($jobs) ?> open positions
    </div>
  </div>

  <!-- Filter Card -->
  <div class="filter-card">
    <div class="search-input-wrapper">
      <input type="text" id="job-search-input" class="search-input" placeholder="Search by job title, company, skills...">
    </div>
    <select id="job-dept-filter" class="filter-select">
      <option value="All">All Departments</option>
      <option value="CSE">CSE</option>
      <option value="EEE">EEE</option>
      <option value="BBA">BBA</option>
      <option value="Data Science">Data Science</option>
    </select>
    <select id="job-type-filter" class="filter-select">
      <option value="All">All Job Types</option>
      <option value="Full-time">Full-time</option>
      <option value="Internship">Internship</option>
    </select>
    <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
  </div>

  <!-- Public Job Grid -->
  <div id="jobs-container" class="cards-grid">
    <?php foreach ($jobs as $j): ?>
      <article class="job-card" data-department="<?= htmlspecialchars($j['department']) ?>" data-type="<?= htmlspecialchars($j['type']) ?>">
        <div>
          <div class="card-top">
            <span class="badge <?= ($j['type'] === 'Internship') ? 'badge-blue' : 'badge-orange' ?>">
              <?= htmlspecialchars($j['type']) ?>
            </span>
            <span class="badge badge-gray"><?= htmlspecialchars($j['department']) ?></span>
          </div>
          <h3 class="job-title"><?= htmlspecialchars($j['title']) ?></h3>
          <div class="company-name"><?= htmlspecialchars($j['company_name']) ?></div>
          <p style="font-size: 0.8125rem; color: var(--slate-500); margin: 0.5rem 0;">📍 <?= htmlspecialchars($j['location']) ?></p>
          <p style="font-size: 0.875rem; color: var(--slate-600); line-height: 1.5; margin-bottom: 0.75rem;">
            <?= htmlspecialchars(substr($j['description'], 0, 140)) ?>...
          </p>
        </div>
        <div class="card-footer">
          <span style="font-weight: 800; color: var(--slate-900);"><?= htmlspecialchars($j['salary']) ?></span>
          <?php if (is_logged_in() && has_role('student')): ?>
            <a href="<?= $app_root ?>/student/job_details.php?id=<?= $j['id'] ?>" class="btn btn-primary btn-sm">
              Apply Now &rarr;
            </a>
          <?php else: ?>
            <a href="<?= $app_root ?>/login.php" class="btn btn-primary btn-sm" title="Log in as UIU student to apply">
              Sign In to Apply &rarr;
            </a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>

<script>
function resetFilters() {
  document.getElementById('job-search-input').value = '';
  document.getElementById('job-dept-filter').value = 'All';
  document.getElementById('job-type-filter').value = 'All';
  document.querySelectorAll('#jobs-container .job-card').forEach(el => el.style.display = '');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
