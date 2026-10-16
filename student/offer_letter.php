<?php
/**
 * UIU Career Portal - Student: Official Employment / Internship Offer Letter
 * Certified digital offer view with verification credentials and PDF export
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'Offer Letter | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$offer = null;
$message = '';

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, c.name as company_name, c.email as company_email, c.phone as company_phone, c.location as company_location,
                   s.full_name as student_name, s.student_id as student_uni_id, s.department as student_dept, s.batch as student_batch
            FROM offer_letters o
            JOIN companies c ON o.company_id = c.id
            JOIN students s ON o.student_id = s.id
            WHERE o.student_id = ?
            ORDER BY o.id DESC LIMIT 1
        ");
        $stmt->execute([$user['profile']['id'] ?? 1]);
        $offer = $stmt->fetch();

        // Handle Offer Acceptance
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $offer) {
            $action = $_POST['action'] ?? '';
            if ($action === 'accept') {
                $upd = $pdo->prepare("UPDATE offer_letters SET status = 'Accepted' WHERE id = ?");
                $upd->execute([$offer['id']]);
                $offer['status'] = 'Accepted';
                $message = 'Congratulations! You have formally accepted this offer letter.';
            } elseif ($action === 'decline') {
                $upd = $pdo->prepare("UPDATE offer_letters SET status = 'Declined' WHERE id = ?");
                $upd->execute([$offer['id']]);
                $offer['status'] = 'Declined';
                $message = 'You have declined this offer letter.';
            }
        }
    } catch (Exception $e) {
        error_log("Error in student/offer_letter.php: " . $e->getMessage());
    }
}

// Fallback demo offer letter
if (!$offer) {
    $offer = [
        'id' => 1,
        'company_name' => 'TechFlow Solutions',
        'company_location' => 'Plot 24, Road 11, Banani, Dhaka-1213',
        'company_email' => 'hr@techflow.com.bd',
        'company_phone' => '+880 2 9876543',
        'student_name' => 'Mugdha Rahman',
        'student_uni_id' => '011201049',
        'student_dept' => 'Computer Science & Engineering (CSE)',
        'student_batch' => 'CSE-49',
        'job_title' => 'Software Engineering Intern (Cloud Platform)',
        'salary_stipend' => 'BDT 20,000 / month (Paid)',
        'joining_date' => '2026-11-01',
        'expiry_date' => '2026-10-25',
        'terms' => "1. Employment Scope: As a Software Engineering Intern, you will participate in agile sprint delivery within the Cloud Engineering team.\n2. Work Hours: 40 hours per week (Hybrid schedule: 3 days on-site at Banani HQ, 2 days remote).\n3. Mentorship: A Principal Architect will oversee your technical development and weekly milestone deliverables.\n4. Academic Compliance: This internship satisfies the official curriculum internship requirement for the UIU Department of CSE.",
        'status' => 'Issued',
        'created_at' => '2026-09-15'
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
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;"><?= htmlspecialchars($offer['student_name']) ?></div>
        <div style="font-size: 0.75rem; color: #94a3b8;">ID: <?= htmlspecialchars($offer['student_uni_id']) ?></div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/student/dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link">💼 Browse Jobs & Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link">📑 My Applications</a></li>
        <li><a href="<?= $app_root ?>/student/profile.php" class="sidebar-link">👤 Student Profile</a></li>
        <li><a href="<?= $app_root ?>/student/resume_builder.php" class="sidebar-link">📄 ATS Resume Builder</a></li>
        <li><a href="<?= $app_root ?>/student/chat.php" class="sidebar-link">💬 Recruiter Messages</a></li>
        <li><a href="<?= $app_root ?>/student/offer_letter.php" class="sidebar-link active">📜 Offer Letters</a></li>
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
        <h1 class="topbar-title">Official Employment Offer Letter</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Verified placement certificate issued by corporate recruitment partner</p>
      </div>
      <div style="display: flex; gap: 0.75rem;">
        <button type="button" class="btn btn-primary" onclick="window.print()">
          🖨️ Download / Print Offer PDF
        </button>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Status Bar -->
    <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--slate-200); padding: 1.25rem 1.75rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 0.875rem; font-weight: 800; color: var(--slate-700);">Offer Status:</span>
        <?php if ($offer['status'] === 'Accepted'): ?>
          <span class="badge badge-green" style="font-size: 0.875rem;">✓ Formally Accepted</span>
        <?php elseif ($offer['status'] === 'Declined'): ?>
          <span class="badge badge-red" style="font-size: 0.875rem;">Declined</span>
        <?php else: ?>
          <span class="badge badge-orange" style="font-size: 0.875rem;">Action Required: Pending Acceptance</span>
        <?php endif; ?>
      </div>

      <?php if ($offer['status'] === 'Issued'): ?>
        <form method="POST" action="<?= $app_root ?>/student/offer_letter.php" style="display: flex; gap: 0.5rem;">
          <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">
            ✓ Officially Accept Offer
          </button>
          <button type="submit" name="action" value="decline" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to decline this offer?')">
            ✕ Decline
          </button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Official Printable Document -->
    <div id="printable-offer-letter" style="background: #ffffff; border: 1px solid var(--slate-300); border-radius: var(--radius-lg); padding: 3.5rem; max-width: 850px; margin: 0 auto; box-shadow: var(--shadow-md); color: #1e293b; font-family: 'Plus Jakarta Sans', Georgia, serif; line-height: 1.6;">
      
      <!-- Letterhead Header -->
      <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--primary); padding-bottom: 1.5rem; margin-bottom: 2rem;">
        <div>
          <div style="font-size: 1.75rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em;">
            <?= htmlspecialchars($offer['company_name']) ?>
          </div>
          <div style="font-size: 0.8125rem; color: var(--slate-500); margin-top: 0.25rem;">
            <?= htmlspecialchars($offer['company_location']) ?><br>
            Email: <?= htmlspecialchars($offer['company_email']) ?> | Tel: <?= htmlspecialchars($offer['company_phone']) ?>
          </div>
        </div>
        <div style="text-align: right;">
          <div style="background: linear-gradient(135deg, #ea580c, #f97316); color: #ffffff; font-weight: 900; font-size: 1rem; padding: 0.4rem 0.85rem; border-radius: var(--radius-sm); display: inline-block;">
            UIU PARTNER
          </div>
          <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.35rem;">
            Ref: UIU-OFFER-<?= str_pad($offer['id'], 5, '0', STR_PAD_LEFT) ?>
          </div>
        </div>
      </div>

      <!-- Date and Recipient -->
      <div style="margin-bottom: 1.75rem; font-size: 0.95rem;">
        <p><strong>Date:</strong> <?= date('F d, Y') ?></p>
        <p style="margin-top: 1rem;">
          <strong>To:</strong><br>
          <strong><?= htmlspecialchars($offer['student_name']) ?></strong><br>
          Student ID: <?= htmlspecialchars($offer['student_uni_id']) ?><br>
          Department of <?= htmlspecialchars($offer['student_dept']) ?><br>
          United International University (UIU), Dhaka, Bangladesh
        </p>
      </div>

      <!-- Subject -->
      <div style="background: #fff7ed; border-left: 4px solid var(--primary); padding: 0.75rem 1rem; margin-bottom: 1.75rem; font-weight: 800; font-size: 1.05rem; color: var(--slate-900);">
        SUBJECT: FORMAL OFFER OF EMPLOYMENT / INTERNSHIP PLACEMENT
      </div>

      <!-- Body -->
      <div style="font-size: 0.95rem; display: flex; flex-direction: column; gap: 1rem; color: #334155;">
        <p>
          Dear <strong><?= htmlspecialchars($offer['student_name']) ?></strong>,
        </p>
        <p>
          On behalf of <strong><?= htmlspecialchars($offer['company_name']) ?></strong>, in collaboration with the Directorate of Career Services and Alumni Affairs at United International University, we are pleased to offer you the position of <strong><?= htmlspecialchars($offer['job_title']) ?></strong>.
        </p>
        <p>
          Your impressive performance in technical evaluations, academic excellence with strong coursework fundamentals at UIU, and enthusiastic interview discussions have demonstrated that you will be a valuable addition to our engineering organization.
        </p>

        <!-- Terms Box -->
        <div style="background-color: #f8fafc; border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem; margin: 0.5rem 0;">
          <h4 style="font-weight: 800; color: var(--slate-900); margin-bottom: 0.5rem; text-transform: uppercase; font-size: 0.8125rem; letter-spacing: 0.05em;">Key Appointment Terms</h4>
          <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.875rem;">
            <li><strong>Designation:</strong> <?= htmlspecialchars($offer['job_title']) ?></li>
            <li><strong>Monthly Remuneration / Stipend:</strong> <?= htmlspecialchars($offer['salary_stipend']) ?></li>
            <li><strong>Reporting Commencement Date:</strong> <?= htmlspecialchars($offer['joining_date']) ?></li>
            <li><strong>Offer Response Deadline:</strong> <?= htmlspecialchars($offer['expiry_date']) ?></li>
          </ul>
        </div>

        <div style="white-space: pre-line; font-size: 0.9rem; color: var(--slate-700);">
          <?= htmlspecialchars($offer['terms']) ?>
        </div>

        <p>
          Please review the offer details and formally signify your acceptance. We look forward to welcoming you into our team!
        </p>
      </div>

      <!-- Signatures Footer -->
      <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 3.5rem; padding-top: 2rem; border-top: 1px solid var(--slate-200);">
        <div>
          <div style="font-family: 'Brush Script MT', cursive, sans-serif; font-size: 1.75rem; color: var(--primary);">Farhan Chowdhury</div>
          <div style="font-weight: 800; font-size: 0.875rem; color: var(--slate-900);">Head of Talent Acquisition</div>
          <div style="font-size: 0.8125rem; color: var(--slate-500);"><?= htmlspecialchars($offer['company_name']) ?></div>
        </div>
        <div style="text-align: right;">
          <div style="font-family: 'Brush Script MT', cursive, sans-serif; font-size: 1.75rem; color: #1e293b;">Dr. S. Jenkins</div>
          <div style="font-weight: 800; font-size: 0.875rem; color: var(--slate-900);">Director, Career Services</div>
          <div style="font-size: 0.8125rem; color: var(--slate-500);">United International University</div>
        </div>
      </div>

    </div>
  </main>
</div>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  #printable-offer-letter, #printable-offer-letter * {
    visibility: visible;
  }
  #printable-offer-letter {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
  }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
