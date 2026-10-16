<?php
/**
 * UIU Career Portal - Registration Page
 * Dual registration portal for Students and Corporate Recruiters
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$app_root = get_app_root();
$page_title = 'Register | UIU Career Portal';
$page_desc = 'Register for the United International University Internship and Career Placement Portal.';

$error = '';
$success = '';
$account_type = $_GET['type'] ?? 'student';
if (!in_array($account_type, ['student', 'company'])) {
    $account_type = 'student';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_type = $_POST['account_type'] ?? 'student';
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $pdo = get_db_connection();
        if ($pdo) {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = 'This email address is already registered in our system. Please sign in or use another email.';
            } else {
                try {
                    $role = ($account_type === 'company') ? 'company' : 'student';
                    $status = ($account_type === 'company') ? 'pending' : 'active';

                    if ($role === 'student') {
                        $studentId = trim($_POST['student_id'] ?? '');
                        $fullName = trim($_POST['full_name'] ?? '');
                        $dept = trim($_POST['department'] ?? 'CSE');
                        $batch = trim($_POST['batch'] ?? 'CSE-50');
                        $cgpa = floatval($_POST['cgpa'] ?? 3.50);
                        $phone = trim($_POST['phone'] ?? '+880 1700-000000');

                        if (empty($studentId)) {
                            throw new Exception('Student ID is required (e.g. 011211055).');
                        }
                        if (empty($fullName)) {
                            throw new Exception('Full Name is required.');
                        }

                        // Check if student_id already registered
                        $checkStudent = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
                        $checkStudent->execute([$studentId]);
                        if ($checkStudent->fetch()) {
                            throw new Exception("Student ID '{$studentId}' is already registered in the system.");
                        }

                        $pdo->beginTransaction();
                        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                        $userStmt = $pdo->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)");
                        $userStmt->execute([$email, $hashedPassword, $role, $status]);
                        $userId = $pdo->lastInsertId();

                        $sStmt = $pdo->prepare("INSERT INTO students (user_id, student_id, full_name, department, batch, cgpa, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $sStmt->execute([$userId, $studentId, $fullName, $dept, $batch, $cgpa, $phone]);
                        $studentDbId = $pdo->lastInsertId();

                        // Ensure resumes table exists before inserting so it never crashes
                        $pdo->exec("CREATE TABLE IF NOT EXISTS `resumes` (
                          `id` INT AUTO_INCREMENT PRIMARY KEY,
                          `student_id` INT NOT NULL UNIQUE,
                          `template_id` VARCHAR(50) DEFAULT 'classic',
                          `full_name` VARCHAR(150) NOT NULL,
                          `email` VARCHAR(150) NOT NULL,
                          `phone` VARCHAR(50) NOT NULL,
                          `degree` VARCHAR(150) NOT NULL,
                          `university` VARCHAR(150) NOT NULL,
                          `cgpa` VARCHAR(20) NOT NULL,
                          `skills` TEXT NOT NULL,
                          `projects` TEXT NOT NULL,
                          `certifications` TEXT NOT NULL,
                          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
                        ) ENGINE=InnoDB");

                        // Seed initial resume
                        try {
                            $rStmt = $pdo->prepare("INSERT INTO resumes (student_id, full_name, email, phone, degree, university, cgpa, skills, projects, certifications) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $rStmt->execute([$studentDbId, $fullName, $email, $phone, "B.Sc. in {$dept}", "United International University", (string)$cgpa, "Web Development, Problem Solving, Git", "Academic Capstone Project", "Undergraduate Enrollment"]);
                        } catch (Exception $re) {
                            error_log("Resume seed note: " . $re->getMessage());
                        }

                        $pdo->commit();

                        login_user(['id' => $userId, 'email' => $email, 'role' => 'student'], [
                            'id' => $userId, 'full_name' => $fullName, 'student_id' => $studentId, 'department' => $dept, 'batch' => $batch, 'cgpa' => $cgpa
                        ]);
                        $_SESSION['flash_success'] = "Welcome, {$fullName}! Your account has been registered successfully.";
                        header("Location: {$app_root}/student/dashboard.php");
                        exit;

                    } elseif ($role === 'company') {
                        $companyName = trim($_POST['company_name'] ?? '');
                        $sector = trim($_POST['sector'] ?? 'Software & IT');
                        $industry = trim($_POST['industry'] ?? 'Technology Services');
                        $location = trim($_POST['location'] ?? 'Dhaka, Bangladesh');
                        $website = trim($_POST['website'] ?? 'https://company.example.com');
                        $phone = trim($_POST['phone'] ?? '+880 2 0000000');
                        $desc = trim($_POST['description'] ?? 'Technology and recruitment partner.');

                        if (empty($companyName)) {
                            throw new Exception('Company / Organization Name is required.');
                        }

                        $pdo->beginTransaction();
                        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                        $userStmt = $pdo->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)");
                        $userStmt->execute([$email, $hashedPassword, $role, $status]);
                        $userId = $pdo->lastInsertId();

                        $cStmt = $pdo->prepare("INSERT INTO companies (user_id, name, sector, industry, location, website, email, phone, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
                        $cStmt->execute([$userId, $companyName, $sector, $industry, $location, $website, $email, $phone, $desc]);

                        $pdo->commit();
                        $success = 'Company registered successfully! Your account has been submitted for verification by the UIU Career Services Office.';
                    }

                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $errorMsg = $e->getMessage();
                    if (strpos($errorMsg, 'Duplicate entry') !== false) {
                        if (strpos($errorMsg, 'student_id') !== false) {
                            $error = 'This Student ID is already registered in the system.';
                        } elseif (strpos($errorMsg, 'email') !== false) {
                            $error = 'This email address is already registered in the system.';
                        } else {
                            $error = 'A duplicate record already exists with the provided information.';
                        }
                    } else {
                        $error = 'Registration failed: ' . $errorMsg;
                    }
                }
            }
        } else {
            // Local standalone / demo mode
            if ($account_type === 'student') {
                $fullName = trim($_POST['full_name'] ?? 'New Student');
                $studentId = trim($_POST['student_id'] ?? '011211055');
                $dept = trim($_POST['department'] ?? 'CSE');
                $batch = trim($_POST['batch'] ?? 'CSE-50');
                $cgpa = floatval($_POST['cgpa'] ?? 3.75);

                login_user(['id' => 99, 'email' => $email, 'role' => 'student'], [
                    'id' => 99, 'full_name' => $fullName, 'student_id' => $studentId, 'department' => $dept, 'batch' => $batch, 'cgpa' => $cgpa
                ]);
                $_SESSION['flash_success'] = "Welcome, {$fullName}! Your account has been registered successfully in demo mode.";
                header("Location: {$app_root}/student/dashboard.php");
                exit;
            } else {
                $success = 'Company registered successfully in demo mode! You can now proceed to log in.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div style="min-height: calc(100vh - 120px); display: flex; align-items: center; justify-content: center; padding: 2.5rem 1.25rem; background: linear-gradient(135deg, #fff7ed 0%, #faf8f5 100%);">
  <div style="background: #ffffff; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); border: 1px solid var(--slate-200); width: 100%; max-width: 620px; padding: 2.5rem;">
    
    <div style="text-align: center; margin-bottom: 2rem;">
      <div class="brand-badge" style="display: inline-block; margin-bottom: 0.75rem; font-size: 1.25rem;">UIU</div>
      <h1 style="font-size: 1.75rem; font-weight: 900; color: var(--slate-900); margin-bottom: 0.25rem;">Create Your Account</h1>
      <p style="font-size: 0.875rem; color: var(--slate-500);">Join United International University official recruitment ecosystem</p>
    </div>

    <!-- Tab Selector -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem; background-color: var(--slate-100); padding: 0.35rem; border-radius: var(--radius-md);">
      <button type="button" id="tab-student" class="btn <?= ($account_type === 'student') ? 'btn-primary' : 'btn-outline' ?>" style="flex: 1; border: none;" onclick="setAccountType('student')">
        🎓 Student Registration
      </button>
      <button type="button" id="tab-company" class="btn <?= ($account_type === 'company') ? 'btn-primary' : 'btn-outline' ?>" style="flex: 1; border: none;" onclick="setAccountType('company')">
        🏢 Corporate Recruiter
      </button>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success" style="margin-bottom: 1.5rem;">
        <?= htmlspecialchars($success) ?>
        <div style="margin-top: 0.75rem;">
          <a href="<?= $app_root ?>/login.php" class="btn btn-primary btn-sm">Proceed to Login &rarr;</a>
        </div>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= $app_root ?>/register.php" id="registration-form">
      <input type="hidden" name="account_type" id="account_type" value="<?= htmlspecialchars($account_type) ?>">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="email" class="form-control" required placeholder="name@domain.edu.bd" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control" required minlength="6" placeholder="Minimum 6 characters">
        </div>
      </div>

      <!-- Student Fields -->
      <div id="student-fields" style="<?= ($account_type === 'student') ? 'display: block;' : 'display: none;' ?>">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" id="student_full_name" class="form-control" placeholder="e.g. Mugdha Rahman" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" <?= ($account_type === 'student') ? 'required' : '' ?>>
          </div>
          <div class="form-group">
            <label class="form-label">Student ID *</label>
            <input type="text" name="student_id" id="student_id" class="form-control" placeholder="e.g. 011211055" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" <?= ($account_type === 'student') ? 'required' : '' ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Department *</label>
            <select name="department" class="form-control">
              <option value="CSE" <?= (($_POST['department'] ?? '') === 'CSE') ? 'selected' : '' ?>>Computer Science & Engineering (CSE)</option>
              <option value="EEE" <?= (($_POST['department'] ?? '') === 'EEE') ? 'selected' : '' ?>>Electrical & Electronic Engineering (EEE)</option>
              <option value="BBA" <?= (($_POST['department'] ?? '') === 'BBA') ? 'selected' : '' ?>>Business Administration (BBA)</option>
              <option value="Data Science" <?= (($_POST['department'] ?? '') === 'Data Science') ? 'selected' : '' ?>>Data Science & Analytics</option>
              <option value="Civil" <?= (($_POST['department'] ?? '') === 'Civil') ? 'selected' : '' ?>>Civil Engineering</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Batch *</label>
            <input type="text" name="batch" class="form-control" placeholder="e.g. CSE-50" value="<?= htmlspecialchars($_POST['batch'] ?? 'CSE-50') ?>">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">CGPA *</label>
            <input type="number" step="0.01" min="0" max="4.00" name="cgpa" class="form-control" placeholder="3.75" value="<?= htmlspecialchars($_POST['cgpa'] ?? '3.75') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Contact Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+880 1712-345678" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Company Fields -->
      <div id="company-fields" style="<?= ($account_type === 'company') ? 'display: block;' : 'display: none;' ?>">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Company / Organization Name *</label>
            <input type="text" name="company_name" id="company_name" class="form-control" placeholder="e.g. Brain Station 23" value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>" <?= ($account_type === 'company') ? 'required' : '' ?>>
          </div>
          <div class="form-group">
            <label class="form-label">Industry Sector *</label>
            <input type="text" name="sector" class="form-control" placeholder="e.g. Software & IT, Telecom" value="<?= htmlspecialchars($_POST['sector'] ?? 'Software & IT') ?>">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Office Location *</label>
            <input type="text" name="location" class="form-control" placeholder="e.g. Banani, Dhaka" value="<?= htmlspecialchars($_POST['location'] ?? 'Dhaka, Bangladesh') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Official Website</label>
            <input type="url" name="website" class="form-control" placeholder="https://example.com" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Company Overview</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Brief description of products, services, and engineering domains."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 1rem; margin-top: 1rem;">
        Complete Registration &rarr;
      </button>
    </form>

    <div style="margin-top: 1.5rem; text-align: center; font-size: 0.875rem; color: var(--slate-600);">
      Already have an account? <a href="<?= $app_root ?>/login.php" style="color: var(--primary); font-weight: 800; text-decoration: none;">Sign In</a>
    </div>

  </div>
</div>

<script>
function setAccountType(type) {
  const isStudent = (type === 'student');
  document.getElementById('account_type').value = type;
  
  const studentFields = document.getElementById('student-fields');
  const companyFields = document.getElementById('company-fields');
  const tabStudent = document.getElementById('tab-student');
  const tabCompany = document.getElementById('tab-company');

  if (studentFields) studentFields.style.display = isStudent ? 'block' : 'none';
  if (companyFields) companyFields.style.display = isStudent ? 'none' : 'block';
  
  if (tabStudent) tabStudent.className = isStudent ? 'btn btn-primary' : 'btn btn-outline';
  if (tabCompany) tabCompany.className = isStudent ? 'btn btn-outline' : 'btn btn-primary';

  const studentNameInput = document.getElementById('student_full_name');
  const studentIdInput = document.getElementById('student_id');
  const companyNameInput = document.getElementById('company_name');

  if (studentNameInput) studentNameInput.required = isStudent;
  if (studentIdInput) studentIdInput.required = isStudent;
  if (companyNameInput) companyNameInput.required = !isStudent;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
