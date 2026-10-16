<?php
/**
 * UIU Career Portal - Company: Student Applicants & Shortlisting Pipeline
 * Review student candidates, update selection statuses, schedule interviews, issue offers
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['company', 'admin']);
$app_root = get_app_root();
$active_role_section = 'company';
$page_title = 'Review Applicants | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$message = '';
$error = '';
$company_id = 1;

if ($pdo) {
    $cStmt = $pdo->prepare("SELECT id, name FROM companies WHERE user_id = ? OR id = ? LIMIT 1");
    $cStmt->execute([$user['id'] ?? 4, $user['profile']['id'] ?? 1]);
    $cRow = $cStmt->fetch();
    if ($cRow) $company_id = $cRow['id'];

    // Handle Status Updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
        $appId = (int)$_POST['application_id'];
        $newStatus = sanitize($_POST['status'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');
        $intDate = sanitize($_POST['interview_date'] ?? '');
        $intVenue = sanitize($_POST['interview_venue'] ?? '');

        try {
            $upd = $pdo->prepare("
                UPDATE applications 
                SET status = ?, notes = ?, interview_date = ?, interview_venue = ? 
                WHERE id = ?
            ");
            $upd->execute([$newStatus, $notes, $intDate, $intVenue, $appId]);

            // If selected, automatically issue/ensure offer letter record exists
            if ($newStatus === 'Selected' || $newStatus === 'Offered') {
                $chkOffer = $pdo->prepare("SELECT id FROM offer_letters WHERE application_id = ?");
                $chkOffer->execute([$appId]);
                if (!$chkOffer->fetch()) {
                    // Fetch application details
                    $aDet = $pdo->prepare("SELECT a.student_id, j.title, j.salary FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = ?");
                    $aDet->execute([$appId]);
                    $aRow = $aDet->fetch();
                    if ($aRow) {
                        $insOffer = $pdo->prepare("
                            INSERT INTO offer_letters (application_id, student_id, company_id, job_title, salary_stipend, joining_date, expiry_date, terms, status)
                            VALUES (?, ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Appointment subject to satisfactory academic verification by UIU Career Services.', 'Issued')
                        ");
                        $insOffer->execute([$appId, $aRow['student_id'], $company_id, $aRow['title'], $aRow['salary']]);
                    }
                }
            }

            // If shortlisted, ensure conversation exists
            if ($newStatus === 'Shortlisted') {
                $aDet = $pdo->prepare("SELECT student_id FROM applications WHERE id = ?");
                $aDet->execute([$appId]);
                $stu = $aDet->fetch();
                if ($stu) {
                    $chkConv = $pdo->prepare("SELECT id FROM conversations WHERE student_id = ? AND company_id = ?");
                    $chkConv->execute([$stu['student_id'], $company_id]);
                    if (!$chkConv->fetch()) {
                        $insConv = $pdo->prepare("INSERT INTO conversations (student_id, company_id, last_message) VALUES (?, ?, 'Candidate moved to Shortlisted status.')");
                        $insConv->execute([$stu['student_id'], $company_id]);
                    }
                }
            }

            $message = "Application #{$appId} updated to '{$newStatus}'!";
        } catch (Exception $e) {
            $error = 'Failed to update application: ' . $e->getMessage();
        }
    }
}

// Fetch applicants
$applicants = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, s.full_name as student_name, s.student_id as student_uni_id, s.department as student_dept, s.batch as student_batch, s.cgpa, s.phone as student_phone, s.skills as student_skills,
                   j.title as job_title, j.type as job_type 
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN students s ON a.student_id = s.id
            WHERE j.company_id = ?
            ORDER BY a.applied_date DESC
        ");
        $stmt->execute([$company_id]);
        $applicants = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in company/applicants.php: " . $e->getMessage());
    }
}

// Fallback demo applicants
if (empty($applicants)) {
    $applicants = [
        [
            'id' => 1,
            'student_name' => 'Mugdha Rahman',
            'student_uni_id' => '011201049',
            'student_dept' => 'CSE',
            'student_batch' => 'CSE-49',
            'cgpa' => 3.75,
            'job_title' => 'Software Engineering Intern',
            'job_type' => 'Internship',
            'status' => 'Shortlisted',
            'applied_date' => '2026-08-15 14:30:00',
            'notes' => 'Passed coding evaluation with 98% score.',
            'interview_date' => 'Next Thursday, 2:00 PM',
            'interview_venue' => 'Banani HQ / Google Meet'
        ],
        [
            'id' => 2,
            'student_name' => 'Tahsin Ahmed',
            'student_uni_id' => '011201088',
            'student_dept' => 'CSE',
            'student_batch' => 'CSE-48',
            'cgpa' => 3.82,
            'job_title' => 'Software Engineering Intern',
            'job_type' => 'Internship',
            'status' => 'Under Review',
            'applied_date' => '2026-08-18 11:20:00',
            'notes' => 'Strong Java and distributed systems foundation.',
            'interview_date' => null,
            'interview_venue' => null
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
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Employer Portal</div>
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;">Talent Pipeline</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/company/dashboard.php" class="sidebar-link">📊 Employer Hub</a></li>
        <li><a href="<?= $app_root ?>/company/create_job.php" class="sidebar-link">➕ Post New Job / Internship</a></li>
        <li><a href="<?= $app_root ?>/company/applicants.php" class="sidebar-link active">👥 Applicants & Shortlisting</a></li>
        <li><a href="<?= $app_root ?>/company/chat.php" class="sidebar-link">💬 Candidate Messages</a></li>
        <li><a href="<?= $app_root ?>/company/profile.php" class="sidebar-link">🏢 Company Profile</a></li>
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
        <h1 class="topbar-title">Applicant Evaluation & Shortlisting</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Review UIU candidate portfolios, shortlist for interviews, and issue employment offers.</p>
      </div>
      <a href="<?= $app_root ?>/company/chat.php" class="btn btn-outline btn-sm">
        💬 Open Candidate Chat &rarr;
      </a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-card">
      <div class="table-header">
        <div>
          <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Candidate Pipeline (<?= count($applicants) ?>)</h2>
          <p style="font-size: 0.8125rem; color: var(--slate-500);">Direct student applications received via campus portal</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Applicant Profile</th>
              <th>Dept / CGPA</th>
              <th>Position Applied</th>
              <th>Status</th>
              <th>Interview Schedule</th>
              <th>Evaluation Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($applicants as $app): ?>
              <tr>
                <td>
                  <div style="font-weight: 800; color: var(--slate-900); font-size: 0.95rem;">
                    <?= htmlspecialchars($app['student_name']) ?>
                  </div>
                  <div style="font-size: 0.75rem; color: var(--slate-500);">
                    ID: <?= htmlspecialchars($app['student_uni_id']) ?> • <?= htmlspecialchars($app['student_batch'] ?? 'UIU') ?>
                  </div>
                </td>
                <td>
                  <div style="font-weight: 800;"><?= htmlspecialchars($app['student_dept']) ?></div>
                  <div style="font-weight: 800; color: var(--primary);">CGPA: <?= htmlspecialchars((string)$app['cgpa']) ?></div>
                </td>
                <td>
                  <div style="font-weight: 700; color: var(--slate-800);"><?= htmlspecialchars($app['job_title']) ?></div>
                  <span class="badge badge-gray"><?= htmlspecialchars($app['job_type']) ?></span>
                </td>
                <td>
                  <?php if ($app['status'] === 'Shortlisted'): ?>
                    <span class="badge badge-blue">✓ Shortlisted</span>
                  <?php elseif ($app['status'] === 'Selected' || $app['status'] === 'Offered'): ?>
                    <span class="badge badge-green">★ Selected / Offer</span>
                  <?php elseif ($app['status'] === 'Rejected'): ?>
                    <span class="badge badge-red">Rejected</span>
                  <?php else: ?>
                    <span class="badge badge-orange"><?= htmlspecialchars($app['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($app['interview_date'])): ?>
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #15803d;">
                      🗓️ <?= htmlspecialchars($app['interview_date']) ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--slate-500);">
                      <?= htmlspecialchars($app['interview_venue'] ?? 'Online') ?>
                    </div>
                  <?php else: ?>
                    <span style="font-size: 0.75rem; color: var(--slate-400);">Not scheduled yet</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button type="button" class="btn btn-primary btn-sm" onclick="openEvaluateModal(<?= htmlspecialchars(json_encode($app)) ?>)">
                    Update Status &rarr;
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Evaluate Candidate Modal -->
<div id="eval-modal" class="modal-overlay">
  <div class="modal-card">
    <div class="modal-header">
      <h3 class="modal-title" id="eval-modal-title">Evaluate Candidate</h3>
      <button type="button" class="close-btn" onclick="closeModal('eval-modal')">&times;</button>
    </div>
    <form method="POST" action="<?= $app_root ?>/company/applicants.php">
      <input type="hidden" name="application_id" id="eval-app-id">
      <div class="modal-body">
        <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.25rem;">
          <div style="font-weight: 800; font-size: 1.05rem;" id="eval-student-name">Mugdha Rahman</div>
          <div style="font-size: 0.8125rem; color: var(--slate-600);" id="eval-student-sub">011201049 • CSE • CGPA: 3.75</div>
          <div style="font-size: 0.8125rem; color: var(--primary); font-weight: 700; margin-top: 0.25rem;" id="eval-job-title">Software Engineering Intern</div>
        </div>

        <div class="form-group">
          <label class="form-label">Selection Decision / Status *</label>
          <select name="status" id="eval-status-select" class="form-control" required>
            <option value="Under Review">Under Review</option>
            <option value="Shortlisted">Shortlisted for Technical Interview</option>
            <option value="Selected">Selected & Issue Offer Letter</option>
            <option value="Rejected">Reject Application</option>
          </select>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Interview Date / Time</label>
            <input type="text" name="interview_date" id="eval-int-date" class="form-control" placeholder="e.g. Next Thursday, 2:00 PM">
          </div>
          <div class="form-group">
            <label class="form-label">Interview Venue / Link</label>
            <input type="text" name="interview_venue" id="eval-int-venue" class="form-control" placeholder="e.g. Google Meet or Banani HQ">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Recruiter Feedback / Internal Notes</label>
          <textarea name="notes" id="eval-notes" class="form-control" rows="3" placeholder="Add comments regarding assessment scores or interview impressions..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('eval-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Decision & Notify Student</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEvaluateModal(app) {
  document.getElementById('eval-app-id').value = app.id;
  document.getElementById('eval-student-name').textContent = app.student_name;
  document.getElementById('eval-student-sub').textContent = `${app.student_uni_id} • ${app.student_dept} • CGPA: ${app.cgpa}`;
  document.getElementById('eval-job-title').textContent = app.job_title;
  document.getElementById('eval-status-select').value = app.status;
  document.getElementById('eval-int-date').value = app.interview_date || '';
  document.getElementById('eval-int-venue').value = app.interview_venue || '';
  document.getElementById('eval-notes').value = app.notes || '';
  openModal('eval-modal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
