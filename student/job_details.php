<?php
/**
 * UIU Career Portal - Student: Job Details Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'Job Details | UIU Career Portal';

$job_id = (int)($_GET['id'] ?? 1);
$pdo = get_db_connection();
$job = null;

if ($pdo && $job_id > 0) {
    $stmt = $pdo->prepare("SELECT j.*, c.name as company_name, c.logo as company_logo, c.sector as company_sector, c.location as company_location, c.website as company_website, c.description as company_overview
                           FROM jobs j
                           JOIN companies c ON j.company_id = c.id
                           WHERE j.id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();
}

if (!$job) {
    // Demo fallback
    $job = [
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
        'description' => 'We are seeking an enthusiastic Software Engineering Intern to join our cloud platform engineering group. You will work on microservices architecture and client dashboards with dedicated mentor guidance.',
        'requirements' => "Undergraduate in CSE or Data Science\nBasic knowledge of web development (JavaScript, HTML, CSS)\nUnderstanding of Data Structures & Algorithms\nFamiliar with Git version control",
        'responsibilities' => "Assist senior engineers in developing and maintaining web services\nWrite clean, testable, and maintainable code\nParticipate in daily agile standups and architectural reviews",
        'company_overview' => 'Leading software technology consultancy building enterprise solutions for fintech and telecom across Southeast Asia.'
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
        <div style="font-size: 1.125rem; font-weight: 900;">Mugdha Rahman</div>
        <div style="font-size: 0.75rem; color: #ffedd5;">ID: 011201049 • CSE-49</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/student/dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link active">💼 Browse Jobs</a></li>
        <li><a href="<?= $app_root ?>/student/internships.php" class="sidebar-link">🎓 Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link">📄 My Applications</a></li>
        <li><a href="<?= $app_root ?>/student/companies.php" class="sidebar-link">🏢 Partner Companies</a></li>
        <li><a href="<?= $app_root ?>/student/resume.php" class="sidebar-link">📝 Resume Builder</a></li>
        <li><a href="<?= $app_root ?>/student/chat.php" class="sidebar-link">💬 Chat & Messages</a></li>
        <li><a href="<?= $app_root ?>/student/profile.php" class="sidebar-link">👤 My Profile</a></li>
      </ul>
    </div>
    <div>
      <a href="<?= $app_root ?>/logout.php" class="sidebar-link" style="color: #fecaca;">🚪 Log Out</a>
    </div>
  </aside>

  <main class="portal-content">
    <div style="margin-bottom: 1.5rem;">
      <a href="<?= $app_root ?>/student/jobs.php" style="font-size: 0.8125rem; font-weight: 700; color: var(--primary);">
        &larr; Back to Job Postings
      </a>
    </div>

    <div class="job-card" style="padding: 2.5rem; max-width: 900px;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid var(--slate-200); padding-bottom: 1.5rem; margin-bottom: 2rem;">
        <div>
          <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
            <span class="badge badge-blue"><?= htmlspecialchars($job['type']) ?></span>
            <span class="badge badge-orange"><?= htmlspecialchars($job['department']) ?></span>
            <span class="badge badge-green">✓ UIU Verified</span>
          </div>
          <h1 style="font-size: 1.875rem; font-weight: 900; color: var(--slate-900);"><?= htmlspecialchars($job['title']) ?></h1>
          <p style="font-size: 1.125rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;">
            <?= htmlspecialchars($job['company_name']) ?>
          </p>
          <p style="font-size: 0.875rem; color: var(--slate-500); margin-top: 0.25rem;">
            📍 <?= htmlspecialchars($job['location']) ?> (<?= htmlspecialchars($job['workplace'] ?? 'Hybrid') ?>)
          </p>
        </div>
        <div style="text-align: right;">
          <div style="font-size: 1.5rem; font-weight: 900; color: var(--slate-900);">
            <?= htmlspecialchars($job['salary']) ?>
          </div>
          <div style="font-size: 0.75rem; color: var(--slate-400); margin-top: 0.25rem;">
            Deadline: <?= htmlspecialchars($job['deadline']) ?>
          </div>
          <div style="margin-top: 1rem;">
            <a href="<?= $app_root ?>/student/apply.php?id=<?= $job['id'] ?>" class="btn btn-primary" style="padding: 0.65rem 1.5rem;">
              Apply Now &rarr;
            </a>
          </div>
        </div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 2rem;">
        <div>
          <h3 style="font-size: 1.125rem; font-weight: 800; color: var(--slate-900); margin-bottom: 0.75rem;">Role Overview</h3>
          <p style="color: var(--slate-600); line-height: 1.7; font-size: 0.9375rem;">
            <?= nl2br(htmlspecialchars($job['description'])) ?>
          </p>
        </div>

        <div>
          <h3 style="font-size: 1.125rem; font-weight: 800; color: var(--slate-900); margin-bottom: 0.75rem;">Responsibilities</h3>
          <p style="color: var(--slate-600); line-height: 1.7; font-size: 0.9375rem;">
            <?= nl2br(htmlspecialchars($job['responsibilities'] ?? '')) ?>
          </p>
        </div>

        <div>
          <h3 style="font-size: 1.125rem; font-weight: 800; color: var(--slate-900); margin-bottom: 0.75rem;">Candidate Requirements</h3>
          <p style="color: var(--slate-600); line-height: 1.7; font-size: 0.9375rem;">
            <?= nl2br(htmlspecialchars($job['requirements'] ?? '')) ?>
          </p>
        </div>

        <div>
          <h3 style="font-size: 1.125rem; font-weight: 800; color: var(--slate-900); margin-bottom: 0.75rem;">Required Competencies & Skills</h3>
          <div class="skills-list">
            <?php foreach (explode(',', $job['skills']) as $s): ?>
              <span class="skill-tag" style="font-size: 0.8125rem; padding: 0.35rem 0.75rem;"><?= htmlspecialchars(trim($s)) ?></span>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="padding-top: 1.5rem; border-top: 1px solid var(--slate-200); display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.8125rem; color: var(--slate-400);">Official campus posting through UIU CDIP</span>
          <a href="<?= $app_root ?>/student/apply.php?id=<?= $job['id'] ?>" class="btn btn-primary">
            Submit Application
          </a>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
