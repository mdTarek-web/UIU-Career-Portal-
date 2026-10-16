<?php
/**
 * UIU Career Portal - Student: ATS Resume Builder & PDF Exporter
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'ATS Resume Builder | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$resume = null;
$message = '';

if ($pdo) {
    $rStmt = $pdo->prepare("SELECT * FROM resumes WHERE student_id = ?");
    $rStmt->execute([$user['profile']['id'] ?? 1]);
    $resume = $rStmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $degree = trim($_POST['degree'] ?? '');
        $university = trim($_POST['university'] ?? 'United International University');
        $cgpa = trim($_POST['cgpa'] ?? '3.75');
        $skills = trim($_POST['skills'] ?? '');
        $projects = trim($_POST['projects'] ?? '');
        $certifications = trim($_POST['certifications'] ?? '');

        if ($resume) {
            $uStmt = $pdo->prepare("
                UPDATE resumes 
                SET full_name = ?, email = ?, phone = ?, degree = ?, university = ?, cgpa = ?, skills = ?, projects = ?, certifications = ?
                WHERE id = ?
            ");
            $uStmt->execute([$fullName, $email, $phone, $degree, $university, $cgpa, $skills, $projects, $certifications, $resume['id']]);
        } else {
            $iStmt = $pdo->prepare("
                INSERT INTO resumes (student_id, full_name, email, phone, degree, university, cgpa, skills, projects, certifications)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $iStmt->execute([$user['profile']['id'] ?? 1, $fullName, $email, $phone, $degree, $university, $cgpa, $skills, $projects, $certifications]);
        }
        $message = 'Resume saved successfully! Your ATS layout is updated below.';
        $rStmt->execute([$user['profile']['id'] ?? 1]);
        $resume = $rStmt->fetch();
    }
}

// Fallback demo resume
if (!$resume) {
    $resume = [
        'full_name' => 'Mugdha Rahman',
        'email' => 'mugdha.rahman@uiu.edu.bd',
        'phone' => '+880 1712-345678',
        'degree' => 'B.Sc. in Computer Science & Engineering (CSE)',
        'university' => 'United International University (UIU)',
        'cgpa' => '3.75',
        'skills' => 'JavaScript (ES6+), TypeScript, React, Node.js, Express, PHP, MySQL, Python, Git, Docker, TailwindCSS',
        'projects' => "• UIU Internship & Career Portal (PHP, MySQL, Vanilla JS)\nEngineered university-wide campus recruitment platform handling multi-role RBAC, real-time messaging, and automated ATS offer letter generator.\n\n• Cloud Microservices Inventory System (Node.js, Docker, MongoDB)\nBuilt high-throughput distributed inventory microservices with token-based authentication and JWT session validation.",
        'certifications' => "• AWS Certified Cloud Practitioner (Amazon Web Services)\n• UIU Dean's List Award for Academic Excellence (Consecutive 4 Trimesters)"
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
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;"><?= htmlspecialchars($resume['full_name']) ?></div>
        <div style="font-size: 0.75rem; color: #94a3b8;">ATS Resume Editor</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/student/dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link">💼 Browse Jobs & Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link">📑 My Applications</a></li>
        <li><a href="<?= $app_root ?>/student/profile.php" class="sidebar-link">👤 Student Profile</a></li>
        <li><a href="<?= $app_root ?>/student/resume_builder.php" class="sidebar-link active">📄 ATS Resume Builder</a></li>
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
        <h1 class="topbar-title">ATS Resume Builder</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Create a recruiter-friendly resume that passes Applicant Tracking Systems (ATS).</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="window.print()">
        🖨️ Download / Print Resume PDF
      </button>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <!-- Editor Form -->
      <div style="background: #ffffff; border-radius: var(--radius-xl); border: 1px solid var(--slate-200); padding: 2rem; box-shadow: var(--shadow-sm);">
        <h2 style="font-size: 1.25rem; font-weight: 900; margin-bottom: 1.5rem; color: var(--slate-900);">Edit Resume Data</h2>
        
        <form method="POST" action="<?= $app_root ?>/student/resume_builder.php">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($resume['full_name']) ?>" required>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($resume['email']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($resume['phone']) ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Degree Title</label>
            <input type="text" name="degree" class="form-control" value="<?= htmlspecialchars($resume['degree']) ?>" required>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">University Name</label>
              <input type="text" name="university" class="form-control" value="<?= htmlspecialchars($resume['university']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">CGPA</label>
              <input type="text" name="cgpa" class="form-control" value="<?= htmlspecialchars($resume['cgpa']) ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Technical Skills (Comma Separated)</label>
            <textarea name="skills" class="form-control" rows="3"><?= htmlspecialchars($resume['skills']) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Projects & Academic Work</label>
            <textarea name="projects" class="form-control" rows="5"><?= htmlspecialchars($resume['projects']) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Certifications & Honors</label>
            <textarea name="certifications" class="form-control" rows="3"><?= htmlspecialchars($resume['certifications']) ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 1rem;">
            Save & Update Resume
          </button>
        </form>
      </div>

      <!-- Live Printable ATS Resume Preview -->
      <div>
        <div id="printable-resume" style="background: #ffffff; border: 1px solid var(--slate-300); border-radius: var(--radius-md); padding: 2.5rem; box-shadow: var(--shadow-md); color: #000000; font-family: 'Times New Roman', Times, serif; line-height: 1.4;">
          
          <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
            <h2 style="font-size: 1.6rem; font-weight: bold; margin-bottom: 0.25rem; text-transform: uppercase;">
              <?= htmlspecialchars($resume['full_name']) ?>
            </h2>
            <div style="font-size: 0.9rem;">
              <?= htmlspecialchars($resume['email']) ?> | <?= htmlspecialchars($resume['phone']) ?> | Dhaka, Bangladesh
            </div>
          </div>

          <!-- Education -->
          <div style="margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.05rem; font-weight: bold; border-bottom: 1px solid #444444; padding-bottom: 0.15rem; margin-bottom: 0.5rem; text-transform: uppercase;">
              EDUCATION
            </h3>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
              <span><?= htmlspecialchars($resume['university']) ?></span>
              <span>Graduation: 2026</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-style: italic;">
              <span><?= htmlspecialchars($resume['degree']) ?></span>
              <span>CGPA: <?= htmlspecialchars($resume['cgpa']) ?> / 4.00</span>
            </div>
          </div>

          <!-- Skills -->
          <div style="margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.05rem; font-weight: bold; border-bottom: 1px solid #444444; padding-bottom: 0.15rem; margin-bottom: 0.5rem; text-transform: uppercase;">
              TECHNICAL SKILLS
            </h3>
            <p style="font-size: 0.95rem;">
              <?= htmlspecialchars($resume['skills']) ?>
            </p>
          </div>

          <!-- Projects -->
          <div style="margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.05rem; font-weight: bold; border-bottom: 1px solid #444444; padding-bottom: 0.15rem; margin-bottom: 0.5rem; text-transform: uppercase;">
              PROJECTS & ACADEMIC WORK
            </h3>
            <div style="font-size: 0.95rem; white-space: pre-line;">
              <?= htmlspecialchars($resume['projects']) ?>
            </div>
          </div>

          <!-- Certifications -->
          <div>
            <h3 style="font-size: 1.05rem; font-weight: bold; border-bottom: 1px solid #444444; padding-bottom: 0.15rem; margin-bottom: 0.5rem; text-transform: uppercase;">
              CERTIFICATIONS & AWARDS
            </h3>
            <div style="font-size: 0.95rem; white-space: pre-line;">
              <?= htmlspecialchars($resume['certifications']) ?>
            </div>
          </div>

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
  #printable-resume, #printable-resume * {
    visibility: visible;
  }
  #printable-resume {
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
