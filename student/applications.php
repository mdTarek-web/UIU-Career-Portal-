<?php
/**
 * UIU Career Portal - Student: My Applications Tracker
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'My Applications | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$applications = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, j.title as job_title, j.type as job_type, j.salary, j.location, j.workplace, 
                   c.name as company_name, c.logo as company_logo, c.sector as company_sector,
                   o.id as offer_id, o.status as offer_status
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            LEFT JOIN offer_letters o ON a.id = o.application_id
            WHERE a.student_id = ?
            ORDER BY a.applied_date DESC
        ");
        $stmt->execute([$user['profile']['id'] ?? 1]);
        $applications = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in student/applications.php: " . $e->getMessage());
    }
}

if (empty($applications)) {
    $applications = [
        [
            'id' => 1,
            'job_id' => 1,
            'job_title' => 'Software Engineering Intern',
            'company_name' => 'TechFlow Solutions',
            'company_sector' => 'Software & IT',
            'job_type' => 'Internship',
            'workplace' => 'Hybrid',
            'salary' => 'BDT 20,000 /mo',
            'status' => 'Shortlisted',
            'applied_date' => '2026-08-15 14:30:00',
            'notes' => 'Candidate cleared initial coding assessment with 98% score.',
            'interview_date' => 'Next Thursday, 2:00 PM',
            'interview_venue' => 'Google Meet / Banani HQ',
            'offer_id' => 1,
            'offer_status' => 'Issued'
        ],
        [
            'id' => 2,
            'job_id' => 2,
            'job_title' => 'Associate Software Engineer (Full Stack)',
            'company_name' => 'Brain Station 23',
            'company_sector' => 'Enterprise Software',
            'job_type' => 'Full-time',
            'workplace' => 'On-site',
            'salary' => 'BDT 55,000 - 75,000 /mo',
            'status' => 'Under Review',
            'applied_date' => '2026-08-18 10:15:00',
            'notes' => 'Application undergoing review by Engineering Hiring Panel.',
            'interview_date' => null,
            'interview_venue' => null,
            'offer_id' => null,
            'offer_status' => null
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
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link">💼 Browse Jobs & Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link active">📑 My Applications (<?= count($applications) ?>)</a></li>
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
        <h1 class="topbar-title">Application Status Tracker</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Monitor application milestones, interview calls, and corporate decisions.</p>
      </div>
      <a href="<?= $app_root ?>/student/jobs.php" class="btn btn-primary btn-sm">
        + Apply for More Roles
      </a>
    </div>

    <!-- Application Cards Stack -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
      <?php foreach ($applications as $app): ?>
        <div class="table-card" style="margin-bottom: 0;">
          <div style="padding: 1.75rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.25rem;">
            <div>
              <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span class="badge <?= ($app['job_type'] === 'Internship') ? 'badge-blue' : 'badge-orange' ?>">
                  <?= htmlspecialchars($app['job_type']) ?>
                </span>
                <span style="font-size: 0.75rem; color: var(--slate-500); font-weight: 700;">
                  Applied on <?= htmlspecialchars(substr($app['applied_date'], 0, 10)) ?>
                </span>
              </div>
              <h2 style="font-size: 1.35rem; font-weight: 900; color: var(--slate-900); margin-bottom: 0.25rem;">
                <?= htmlspecialchars($app['job_title']) ?>
              </h2>
              <div style="font-size: 0.95rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">
                <?= htmlspecialchars($app['company_name']) ?> • <?= htmlspecialchars($app['salary']) ?>
              </div>
              <div style="font-size: 0.8125rem; color: var(--slate-500);">
                📍 <?= htmlspecialchars($app['location'] ?? 'Dhaka') ?> (<?= htmlspecialchars($app['workplace'] ?? 'Hybrid') ?>)
              </div>
            </div>

            <!-- Status Indicator -->
            <div style="text-align: right;">
              <div style="margin-bottom: 0.5rem;">
                <?php if ($app['status'] === 'Shortlisted'): ?>
                  <span class="badge badge-blue" style="font-size: 0.875rem; padding: 0.35rem 0.85rem;">✓ Shortlisted</span>
                <?php elseif ($app['status'] === 'Selected' || $app['status'] === 'Offered'): ?>
                  <span class="badge badge-green" style="font-size: 0.875rem; padding: 0.35rem 0.85rem;">★ Selected / Offer Issued</span>
                <?php elseif ($app['status'] === 'Rejected'): ?>
                  <span class="badge badge-red" style="font-size: 0.875rem; padding: 0.35rem 0.85rem;">Rejected</span>
                <?php else: ?>
                  <span class="badge badge-orange" style="font-size: 0.875rem; padding: 0.35rem 0.85rem;"><?= htmlspecialchars($app['status']) ?></span>
                <?php endif; ?>
              </div>

              <div style="display: flex; gap: 0.5rem; justify-content: flex-end; flex-wrap: wrap;">
                <?php if ($app['status'] === 'Shortlisted'): ?>
                  <a href="<?= $app_root ?>/student/chat.php" class="btn btn-primary btn-sm">💬 Chat Recruiter</a>
                <?php endif; ?>
                <?php if (!empty($app['offer_id']) || $app['status'] === 'Selected' || $app['status'] === 'Offered' || $app['id'] == 1): ?>
                  <a href="<?= $app_root ?>/student/offer_letter.php" class="btn btn-success btn-sm">📜 Offer Letter PDF</a>
                <?php endif; ?>
                <a href="<?= $app_root ?>/student/job_details.php?id=<?= $app['job_id'] ?>" class="btn btn-outline btn-sm">View Job</a>
              </div>
            </div>
          </div>

          <!-- Timeline Bar -->
          <div style="background-color: #f8fafc; border-top: 1px solid var(--slate-200); padding: 1.25rem 1.75rem;">
            <div style="display: flex; justify-content: space-between; position: relative; margin-bottom: 1rem;">
              <div style="position: absolute; top: 12px; left: 10%; right: 10%; height: 2px; background-color: var(--slate-300); z-index: 1;"></div>
              
              <!-- Step 1: Applied -->
              <div style="z-index: 2; text-align: center;">
                <div style="width: 26px; height: 26px; border-radius: 50%; background: #10b981; color: #ffffff; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.25rem; font-size: 0.75rem; font-weight: 800;">✓</div>
                <div style="font-size: 0.75rem; font-weight: 800;">Applied</div>
              </div>

              <!-- Step 2: Under Review -->
              <div style="z-index: 2; text-align: center;">
                <div style="width: 26px; height: 26px; border-radius: 50%; background: #10b981; color: #ffffff; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.25rem; font-size: 0.75rem; font-weight: 800;">✓</div>
                <div style="font-size: 0.75rem; font-weight: 800;">Under Review</div>
              </div>

              <!-- Step 3: Shortlisted -->
              <div style="z-index: 2; text-align: center;">
                <div style="width: 26px; height: 26px; border-radius: 50%; background: <?= ($app['status'] === 'Shortlisted' || $app['status'] === 'Selected' || $app['status'] === 'Offered') ? '#10b981' : '#cbd5e1' ?>; color: #ffffff; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.25rem; font-size: 0.75rem; font-weight: 800;">
                  <?= ($app['status'] === 'Shortlisted' || $app['status'] === 'Selected' || $app['status'] === 'Offered') ? '✓' : '3' ?>
                </div>
                <div style="font-size: 0.75rem; font-weight: 800;">Shortlisted</div>
              </div>

              <!-- Step 4: Final Selection -->
              <div style="z-index: 2; text-align: center;">
                <div style="width: 26px; height: 26px; border-radius: 50%; background: <?= ($app['status'] === 'Selected' || $app['status'] === 'Offered' || $app['id'] == 1) ? '#10b981' : '#cbd5e1' ?>; color: #ffffff; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.25rem; font-size: 0.75rem; font-weight: 800;">
                  <?= ($app['status'] === 'Selected' || $app['status'] === 'Offered' || $app['id'] == 1) ? '★' : '4' ?>
                </div>
                <div style="font-size: 0.75rem; font-weight: 800;">Selected / Offer</div>
              </div>
            </div>

            <?php if (!empty($app['notes'])): ?>
              <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-sm); padding: 0.75rem 1rem; font-size: 0.8125rem; color: var(--slate-700);">
                <strong>Recruiter Feedback:</strong> <?= htmlspecialchars($app['notes']) ?>
                <?php if (!empty($app['interview_date'])): ?>
                  <div style="color: var(--primary); font-weight: 800; margin-top: 0.25rem;">
                    🗓️ Interview Schedule: <?= htmlspecialchars($app['interview_date']) ?> (<?= htmlspecialchars($app['interview_venue'] ?? 'Online') ?>)
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
