<?php
/**
 * UIU Career Portal - Student: Browse Jobs & Internships
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'Browse Jobs & Internships | UIU Career Portal';

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
        error_log("Error in student/jobs.php: " . $e->getMessage());
    }
}

if (empty($jobs)) {
    $jobs = [
        [
            'id' => 1,
            'title' => 'Software Engineering Intern',
            'company_name' => 'TechFlow Solutions',
            'department' => 'CSE',
            'type' => 'Internship',
            'workplace' => 'Hybrid',
            'location' => 'Banani, Dhaka',
            'salary' => 'BDT 20,000 /mo',
            'deadline' => '2026-12-31',
            'skills' => 'JavaScript, React, Node.js, Git',
            'description' => 'Join our core platform engineering team working on scalable cloud microservices. Great mentorship provided for UIU undergraduates.'
        ],
        [
            'id' => 2,
            'title' => 'Associate Software Engineer (Full Stack)',
            'company_name' => 'Brain Station 23',
            'department' => 'CSE',
            'type' => 'Full-time',
            'workplace' => 'On-site',
            'location' => 'Mohakhali DOHS, Dhaka',
            'salary' => 'BDT 55,000 - 75,000 /mo',
            'deadline' => '2026-11-30',
            'skills' => 'Java, Spring Boot, MySQL, REST API',
            'description' => 'Exciting opening for ambitious graduating students and fresh alumni to build enterprise FinTech solutions.'
        ],
        [
            'id' => 3,
            'title' => 'Junior Cloud & DevOps Engineer',
            'company_name' => 'BRAC IT Services',
            'department' => 'CSE',
            'type' => 'Full-time',
            'workplace' => 'Hybrid',
            'location' => 'Gulshan-1, Dhaka',
            'salary' => 'BDT 60,000 /mo',
            'deadline' => '2026-11-15',
            'skills' => 'Docker, Linux, AWS, CI/CD, Python',
            'description' => 'Support high-availability microfinance cloud infrastructure serving millions of daily mobile users.'
        ],
        [
            'id' => 4,
            'title' => 'Frontend Developer (React / JavaScript)',
            'company_name' => 'Chaldal Tech',
            'department' => 'CSE',
            'type' => 'Full-time',
            'workplace' => 'Remote',
            'location' => 'Dhaka (Remote)',
            'salary' => 'BDT 50,000 - 65,000 /mo',
            'deadline' => '2026-12-15',
            'skills' => 'JavaScript, React, CSS3, TailwindCSS',
            'description' => 'Build customer-facing e-commerce storefronts and warehouse management interfaces.'
        ],
        [
            'id' => 5,
            'title' => 'Quality Assurance (QA) Intern',
            'company_name' => 'Therap Services',
            'department' => 'CSE',
            'type' => 'Internship',
            'workplace' => 'On-site',
            'location' => 'Baridhara, Dhaka',
            'salary' => 'BDT 22,000 /mo',
            'deadline' => '2026-12-20',
            'skills' => 'Selenium, Manual Testing, SQL, JIRA',
            'description' => 'Learn test automation and QA methodologies in a multinational healthcare SaaS environment.'
        ]
    ];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="portal-layout">
  <aside class="portal-sidebar">
    <div>
      <div class="sidebar-brand">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Student Portal</div>
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;">Mugdha Rahman</div>
        <div style="font-size: 0.75rem; color: #94a3b8;">ID: 011201049 • CSE-49</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/student/dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link active">💼 Browse Jobs & Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link">📑 My Applications</a></li>
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

  <main class="portal-main">
    <div class="topbar">
      <div>
        <h1 class="topbar-title">Browse Jobs & Internships</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Verified employment opportunities from official UIU corporate partners</p>
      </div>
      <div id="jobs-match-count" style="font-size: 0.875rem; font-weight: 800; color: var(--primary);">
        Showing <?= count($jobs) ?> open positions
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-card">
      <div class="search-input-wrapper">
        <input 
          type="text" 
          id="job-search-input" 
          class="search-input" 
          placeholder="Search by title, company, skills (e.g. Java, Python, React)..."
        >
      </div>
      <select id="job-dept-filter" class="filter-select">
        <option value="All">All Departments</option>
        <option value="CSE">CSE</option>
        <option value="EEE">EEE</option>
        <option value="BBA">BBA</option>
        <option value="Data Science">Data Science</option>
      </select>
      <select id="job-type-filter" class="filter-select">
        <option value="All">All Employment Types</option>
        <option value="Full-time">Full-time</option>
        <option value="Internship">Internship</option>
      </select>
      <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
    </div>

    <!-- Jobs Grid -->
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
            <div class="company-name"><?= htmlspecialchars($j['company_name'] ?? 'Partner Company') ?></div>
            <p style="font-size: 0.8125rem; color: var(--slate-500); margin: 0.5rem 0;">📍 <?= htmlspecialchars($j['location']) ?></p>
            <p style="font-size: 0.875rem; color: var(--slate-600); line-height: 1.5; margin-bottom: 0.75rem;">
              <?= htmlspecialchars(substr($j['description'], 0, 140)) ?>...
            </p>
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
              <?php 
                $skillsArr = is_array($j['skills']) ? $j['skills'] : explode(',', (string)$j['skills']);
                foreach (array_slice($skillsArr, 0, 3) as $sk): 
              ?>
                <span class="badge badge-gray" style="font-size: 0.7rem;"><?= htmlspecialchars(trim($sk)) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="card-footer">
            <div>
              <div style="font-weight: 800; font-size: 0.95rem; color: var(--slate-900);"><?= htmlspecialchars($j['salary']) ?></div>
              <?php if (!empty($j['deadline'])): ?>
                <div style="font-size: 0.7rem; color: var(--slate-500);">Deadline: <?= htmlspecialchars($j['deadline']) ?></div>
              <?php endif; ?>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <a href="<?= $app_root ?>/student/job_details.php?id=<?= $j['id'] ?>" class="btn btn-outline btn-sm">Details</a>
              <a href="<?= $app_root ?>/student/apply.php?job_id=<?= $j['id'] ?>" class="btn btn-primary btn-sm">Apply &rarr;</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </main>
</div>

<script>
function resetFilters() {
  document.getElementById('job-search-input').value = '';
  document.getElementById('job-dept-filter').value = 'All';
  document.getElementById('job-type-filter').value = 'All';
  document.querySelectorAll('#jobs-container .job-card').forEach(el => el.style.display = '');
  document.getElementById('jobs-match-count').textContent = 'Showing <?= count($jobs) ?> open positions';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
