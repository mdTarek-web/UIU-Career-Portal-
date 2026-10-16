<?php
/**
 * UIU Career Portal - Main Homepage (PHP Version)
 * Pure PHP + MySQL + Vanilla JS
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$app_root = get_app_root();
$active_role_section = 'landing';
$page_title = 'UIU Career Portal | United International University';
$page_desc = 'Official career services, internship placement, and employer engagement portal for United International University.';

$pdo = get_db_connection();
$jobs = [];
$companies = [];

if ($pdo) {
    try {
        $jStmt = $pdo->query("SELECT j.*, c.name as company_name, c.logo as company_logo 
                             FROM jobs j 
                             JOIN companies c ON j.company_id = c.id 
                             WHERE j.status = 'Approved' 
                             ORDER BY j.featured DESC, j.id DESC 
                             LIMIT 12");
        $jobs = $jStmt->fetchAll();

        $cStmt = $pdo->query("SELECT * FROM companies WHERE status = 'Verified' ORDER BY health_score DESC LIMIT 8");
        $companies = $cStmt->fetchAll();
    } catch (Exception $e) {
        error_log("Database error on index.php: " . $e->getMessage());
    }
}

// Fallback demo data if MySQL is connecting fresh
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
            'skills' => 'JavaScript, React, CSS3, TailwindCSS',
            'description' => 'Build customer-facing e-commerce storefronts and warehouse management interfaces.'
        ]
    ];
}

if (empty($companies)) {
    $companies = [
        ['id' => 1, 'name' => 'TechFlow Solutions', 'sector' => 'Software & IT', 'location' => 'Banani, Dhaka', 'health_score' => 96, 'description' => 'Enterprise Cloud & Web Systems consulting.'],
        ['id' => 2, 'name' => 'Brain Station 23', 'sector' => 'Enterprise Software', 'location' => 'Mohakhali DOHS, Dhaka', 'health_score' => 98, 'description' => 'Premier software export company with 800+ engineers.'],
        ['id' => 3, 'name' => 'BRAC IT Services', 'sector' => 'FinTech', 'location' => 'Gulshan-1, Dhaka', 'health_score' => 94, 'description' => 'Technology subsidiary delivering modern core banking.'],
        ['id' => 4, 'name' => 'Therap Services', 'sector' => 'Healthcare SaaS', 'location' => 'Baridhara, Dhaka', 'health_score' => 97, 'description' => 'US-based healthcare software engineering center.']
    ];
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- 1. HERO SECTION -->
<section id="home" class="hero-section">
  <div class="hero-grid">
    <div>
      <div class="hero-tag">
        <span>✨</span>
        <span>UIU Centre for Development of IT Professionals (CDIP) & Career Services</span>
      </div>
      <h1 class="hero-title">
        Find Your Future.<br>
        <span>Build Your Career.</span>
      </h1>
      <p class="hero-description">
        Discover verified internships and graduate opportunities from top national and multinational employers. Connecting United International University students directly with industry recruiters.
      </p>
      <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="#jobs" class="btn btn-primary" style="padding: 0.875rem 1.75rem; font-size: 1rem;">
          <span>Find Jobs & Internships</span>
          <span>&rarr;</span>
        </a>
        <a href="#companies" class="btn btn-outline" style="padding: 0.875rem 1.75rem; font-size: 1rem;">
          <span>Explore Partner Companies</span>
        </a>
      </div>
      <div style="display: flex; gap: 1.5rem; margin-top: 1.5rem; font-size: 0.8125rem; font-weight: 700; color: var(--slate-600); flex-wrap: wrap;">
        <span>✓ Verified UIU Corporate MoUs</span>
        <span>✓ Direct Recruiter Interviews</span>
        <span>✓ ATS-Compliant Resume Builder</span>
      </div>
    </div>
    
    <div>
      <div style="background: linear-gradient(135deg, #fed7aa, #fdba74); padding: 1.5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); border: 4px solid #ffffff;">
        <div style="background-color: var(--slate-900); color: #ffffff; padding: 2rem; border-radius: var(--radius-lg);">
          <div style="font-size: 0.8125rem; font-weight: 800; color: #f97316; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">
            United International University
          </div>
          <h2 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 0.75rem;">Official Career Portal</h2>
          <p style="color: var(--slate-300); font-size: 0.875rem; line-height: 1.6;">
            Direct campus recruitment gateway connecting students with verified corporate partners. Pure PHP, MySQL & Vanilla JS architecture.
          </p>
          <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <span class="badge badge-green">✓ Verified Corporate Portal</span>
            <span class="badge badge-orange">★ UIU CDIP Authorized</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 2. STATS METRIC STRIP -->
<section class="stats-strip">
  <div class="stats-grid">
    <div>
      <div class="stat-number">1,250+</div>
      <div class="stat-label">Students Placed</div>
      <div class="stat-sub">Across top multinationals & tech startups</div>
    </div>
    <div>
      <div class="stat-number">85+</div>
      <div class="stat-label">Partner Companies</div>
      <div class="stat-sub">MoU signed corporate recruiters</div>
    </div>
    <div>
      <div class="stat-number">120+</div>
      <div class="stat-label">Active Openings</div>
      <div class="stat-sub">Internships and graduate roles</div>
    </div>
  </div>
</section>

<!-- 3. FEATURED JOBS SECTION -->
<section id="jobs" class="section-wrapper">
  <div class="section-head">
    <div>
      <h2 class="section-title">Featured Jobs & Internships</h2>
      <p class="section-subtitle">Live job vacancies and internships curated for UIU undergraduates and graduates</p>
    </div>
    <div id="jobs-match-count" style="font-size: 0.875rem; font-weight: 700; color: var(--primary);">
      Showing <?= count($jobs) ?> open positions
    </div>
  </div>

  <!-- Filter Control Card -->
  <div class="filter-card">
    <div class="search-input-wrapper">
      <input 
        type="text" 
        id="job-search-input" 
        class="search-input" 
        placeholder="Search jobs by title, company, skills (e.g. React, Java, Cloud)..."
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
      <option value="All">All Job Types</option>
      <option value="Full-time">Full-time</option>
      <option value="Internship">Internship</option>
    </select>
    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
      Reset
    </button>
  </div>

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
          <div class="company-name"><?= htmlspecialchars($j['company_name'] ?? $j['company']) ?></div>
          <p style="font-size: 0.8125rem; color: var(--slate-500); margin: 0.5rem 0;">📍 <?= htmlspecialchars($j['location']) ?></p>
          <p style="font-size: 0.875rem; color: var(--slate-600); line-height: 1.5; margin-bottom: 0.75rem;">
            <?= htmlspecialchars(substr($j['description'], 0, 130)) ?>...
          </p>
          <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
            <?php 
              $skillsArr = is_array($j['skills']) ? $j['skills'] : explode(',', (string)$j['skills']);
              foreach (array_slice($skillsArr, 0, 3) as $sk): 
            ?>
              <span class="badge badge-gray" style="font-size: 0.7rem;"><?= htmlspecialchars(trim($sk)) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card-footer">
          <span style="font-weight: 800; font-size: 0.9rem; color: var(--slate-900);"><?= htmlspecialchars($j['salary']) ?></span>
          <a href="<?= $app_root ?>/student/job_details.php?id=<?= $j['id'] ?>" class="btn btn-primary btn-sm">
            View & Apply &rarr;
          </a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- 4. PARTNER COMPANIES DIRECTORY -->
<section id="companies" style="background-color: var(--slate-100); border-top: 1px solid var(--slate-200); padding: 4rem 1.25rem;">
  <div style="max-width: 1380px; margin: 0 auto;">
    <div class="section-head">
      <div>
        <h2 class="section-title">Partner Companies Directory</h2>
        <p class="section-subtitle">Top multinational enterprises and software leaders hiring from UIU</p>
      </div>
      <a href="<?= $app_root ?>/guest/companies.php" class="btn btn-outline btn-sm">
        View All Partners &rarr;
      </a>
    </div>

    <div class="cards-grid">
      <?php foreach ($companies as $c): ?>
        <div class="company-card">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
            <div style="font-size: 1.5rem;">🏢</div>
            <span class="badge badge-green">✓ Verified Partner</span>
          </div>
          <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.25rem;"><?= htmlspecialchars($c['name']) ?></h3>
          <p style="font-size: 0.8125rem; color: var(--primary); font-weight: 700; margin-bottom: 0.5rem;">
            <?= htmlspecialchars($c['sector']) ?> • <?= htmlspecialchars($c['location']) ?>
          </p>
          <p style="font-size: 0.875rem; color: var(--slate-600); line-height: 1.5; margin-bottom: 1rem;">
            <?= htmlspecialchars(substr($c['description'] ?? 'Official corporate recruitment partner of United International University.', 0, 110)) ?>...
          </p>
          <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--slate-100); padding-top: 0.75rem;">
            <span style="font-size: 0.75rem; color: var(--slate-500); font-weight: 700;">Hiring Score: <?= $c['health_score'] ?? 95 ?>%</span>
            <a href="<?= $app_root ?>/student/jobs.php" class="btn btn-secondary btn-sm">View Openings</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 5. ABOUT SECTION -->
<section id="about" class="section-wrapper" style="border-top: 1px solid var(--slate-200);">
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
    <div>
      <div class="hero-tag">
        <span>🎓</span>
        <span>Directorate of Career Services and Alumni Affairs</span>
      </div>
      <h2 style="font-size: 2.25rem; font-weight: 900; line-height: 1.2; margin-bottom: 1rem;">
        Bridging Classroom Excellence With Global Careers
      </h2>
      <p style="color: var(--slate-600); line-height: 1.6; margin-bottom: 1.25rem;">
        The UIU Career Portal is the centralized hub for undergraduate internships, graduate recruitment, and professional networking. Established in collaboration with CDIP, our portal enables students to prepare ATS-standard resumes, track live application milestones, and directly interface with corporate HR leads.
      </p>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div style="background: #ffffff; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--slate-200);">
          <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">📄</div>
          <h4 style="font-weight: 800; font-size: 0.95rem;">Automated Resumes</h4>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Instant PDF resume generator formatted to corporate ATS standards.</p>
        </div>
        <div style="background: #ffffff; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--slate-200);">
          <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">💬</div>
          <h4 style="font-weight: 800; font-size: 0.95rem;">Direct Messaging</h4>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Engage in verified dialogue with recruiters once shortlisted.</p>
        </div>
      </div>
    </div>
    <div>
      <div style="background: #ffffff; padding: 2rem; border-radius: var(--radius-xl); border: 1px solid var(--slate-200); box-shadow: var(--shadow-md);">
        <h3 style="font-size: 1.25rem; font-weight: 900; margin-bottom: 1rem;">Official Recruitment Workflow</h3>
        <ol style="display: flex; flex-direction: column; gap: 1rem; padding-left: 1.25rem; color: var(--slate-700); font-size: 0.875rem;">
          <li><strong>Student Profile & Resume:</strong> Students build their academic profile and compile their certified resume.</li>
          <li><strong>Job & Internship Application:</strong> Browse verified postings filtered by department (CSE, EEE, BBA) and apply with 1 click.</li>
          <li><strong>Recruiter Evaluation:</strong> Companies review candidate CGPA, portfolios, and move candidates to shortlisted status.</li>
          <li><strong>Direct Communication:</strong> Schedule interviews and coordinate technical evaluations through the secure portal chat.</li>
          <li><strong>Official Placement & Offer:</strong> Formal offer letters issued with verifiable digital signature records.</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<script>
function resetFilters() {
  const sInput = document.getElementById('job-search-input');
  const dFilter = document.getElementById('job-dept-filter');
  const tFilter = document.getElementById('job-type-filter');
  if (sInput) sInput.value = '';
  if (dFilter) dFilter.value = 'All';
  if (tFilter) tFilter.value = 'All';
  document.querySelectorAll('#jobs-container .job-card').forEach(el => el.style.display = '');
  const countBadge = document.getElementById('jobs-match-count');
  if (countBadge) countBadge.textContent = 'Showing <?= count($jobs) ?> open positions';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
