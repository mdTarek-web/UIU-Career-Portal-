<?php
/**
 * UIU Career Portal - Company: Recruiter Chat with Shortlisted Candidates
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['company', 'admin']);
$app_root = get_app_root();
$active_role_section = 'company';
$page_title = 'Candidate Messages | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$company_id = 1;
if ($pdo) {
    $cStmt = $pdo->prepare("SELECT id, name FROM companies WHERE user_id = ? OR id = ? LIMIT 1");
    $cStmt->execute([$user['id'] ?? 4, $user['profile']['id'] ?? 1]);
    $cRow = $cStmt->fetch();
    if ($cRow) $company_id = $cRow['id'];
}

$conversations = [];
$active_conv_id = (int)($_GET['conv_id'] ?? 0);
$messages = [];
$active_student = null;

if ($pdo) {
    try {
        // 1. Fetch conversations for this company
        $cStmt = $pdo->prepare("
            SELECT c.*, s.full_name as student_name, s.student_id as student_uni_id, s.department as student_dept, s.cgpa as student_cgpa
            FROM conversations c
            JOIN students s ON c.student_id = s.id
            WHERE c.company_id = ?
            ORDER BY c.last_message_at DESC
        ");
        $cStmt->execute([$company_id]);
        $conversations = $cStmt->fetchAll();

        if (empty($active_conv_id) && !empty($conversations)) {
            $active_conv_id = (int)$conversations[0]['id'];
        }

        // 2. Handle message posting
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text']) && $active_conv_id > 0) {
            $text = trim($_POST['message_text']);
            if (!empty($text)) {
                $senderName = $user['name'] ?? 'TechFlow Solutions HR';
                $mStmt = $pdo->prepare("
                    INSERT INTO messages (conversation_id, sender_type, sender_name, message_text)
                    VALUES (?, 'company', ?, ?)
                ");
                $mStmt->execute([$active_conv_id, $senderName, $text]);

                $uStmt = $pdo->prepare("UPDATE conversations SET last_message = ?, last_message_at = NOW() WHERE id = ?");
                $uStmt->execute([$text, $active_conv_id]);
            }
        }

        // 3. Fetch messages for active conversation
        if ($active_conv_id > 0) {
            $mStmt = $pdo->prepare("
                SELECT * FROM messages 
                WHERE conversation_id = ? 
                ORDER BY created_at ASC
            ");
            $mStmt->execute([$active_conv_id]);
            $messages = $mStmt->fetchAll();

            foreach ($conversations as $c) {
                if ($c['id'] == $active_conv_id) {
                    $active_student = $c;
                    break;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error in company/chat.php: " . $e->getMessage());
    }
}

// Fallback demo conversation
if (empty($conversations)) {
    $conversations = [
        [
            'id' => 1,
            'student_name' => 'Mugdha Rahman',
            'student_uni_id' => '011201049',
            'student_dept' => 'CSE',
            'student_cgpa' => 3.75,
            'last_message' => 'We look forward to meeting you on Thursday at 2:00 PM.',
            'last_message_at' => date('Y-m-d H:i:s')
        ]
    ];
    $active_conv_id = 1;
    $active_student = $conversations[0];
    $messages = [
        ['sender_type' => 'company', 'sender_name' => 'TechFlow Solutions', 'message_text' => 'Hello Mugdha, congratulations on being shortlisted for Software Engineering Intern role!', 'created_at' => '2026-08-16 10:00:00'],
        ['sender_type' => 'student', 'sender_name' => 'Mugdha Rahman', 'message_text' => 'Thank you so much! I am very excited and preparing for the technical round.', 'created_at' => '2026-08-16 10:15:00'],
        ['sender_type' => 'company', 'sender_name' => 'TechFlow Solutions', 'message_text' => 'We look forward to meeting you on Thursday at 2:00 PM.', 'created_at' => '2026-08-16 11:30:00']
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
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;">Candidate Chat</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/company/dashboard.php" class="sidebar-link">📊 Employer Hub</a></li>
        <li><a href="<?= $app_root ?>/company/create_job.php" class="sidebar-link">➕ Post New Job / Internship</a></li>
        <li><a href="<?= $app_root ?>/company/applicants.php" class="sidebar-link">👥 Applicants & Shortlisting</a></li>
        <li><a href="<?= $app_root ?>/company/chat.php" class="sidebar-link active">💬 Candidate Messages</a></li>
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
        <h1 class="topbar-title">Direct Candidate Communication</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Coordinate technical evaluations and send interview invitations to shortlisted students.</p>
      </div>
    </div>

    <!-- Chat Container -->
    <div class="chat-container">
      
      <!-- Thread List -->
      <div class="chat-sidebar">
        <div style="padding: 1.25rem; border-bottom: 1px solid var(--slate-200); font-weight: 800; font-size: 0.875rem; color: var(--slate-700);">
          Shortlisted Candidates (<?= count($conversations) ?>)
        </div>
        <div style="overflow-y: auto; flex: 1;">
          <?php foreach ($conversations as $c): ?>
            <a href="<?= $app_root ?>/company/chat.php?conv_id=<?= $c['id'] ?>" class="chat-thread <?= ($c['id'] == $active_conv_id) ? 'active' : '' ?>" style="text-decoration: none; display: block; color: inherit;">
              <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.25rem;">
                <span style="font-weight: 800; font-size: 0.95rem; color: var(--slate-900);"><?= htmlspecialchars($c['student_name']) ?></span>
                <span style="font-size: 0.7rem; color: var(--primary); font-weight: 700;">CGPA: <?= htmlspecialchars((string)($c['student_cgpa'] ?? '3.75')) ?></span>
              </div>
              <div style="font-size: 0.75rem; color: var(--slate-500); margin-bottom: 0.25rem;">
                <?= htmlspecialchars($c['student_uni_id']) ?> • <?= htmlspecialchars($c['student_dept']) ?>
              </div>
              <p style="font-size: 0.75rem; color: var(--slate-600); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <?= htmlspecialchars($c['last_message'] ?? 'Click to start conversation') ?>
              </p>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Messages Pane -->
      <div class="chat-main">
        <?php if ($active_student): ?>
          <div class="chat-header">
            <div>
              <div style="font-weight: 900; font-size: 1.1rem; color: var(--slate-900);"><?= htmlspecialchars($active_student['student_name']) ?></div>
              <div style="font-size: 0.75rem; color: var(--slate-500);">
                ID: <?= htmlspecialchars($active_student['student_uni_id']) ?> • <?= htmlspecialchars($active_student['student_dept']) ?> • CGPA: <?= htmlspecialchars((string)($active_student['student_cgpa'] ?? '3.75')) ?>
              </div>
            </div>
            <a href="<?= $app_root ?>/company/applicants.php" class="btn btn-outline btn-sm">Pipeline &rarr;</a>
          </div>

          <div class="chat-messages" id="chat-box">
            <?php foreach ($messages as $m): ?>
              <?php $isMe = ($m['sender_type'] === 'company'); ?>
              <div class="chat-bubble <?= $isMe ? 'sent' : 'received' ?>">
                <div style="font-size: 0.7rem; font-weight: 800; opacity: 0.85; margin-bottom: 0.25rem;">
                  <?= htmlspecialchars($m['sender_name']) ?>
                </div>
                <div><?= htmlspecialchars($m['message_text']) ?></div>
                <div style="font-size: 0.65rem; opacity: 0.75; text-align: right; margin-top: 0.35rem;">
                  <?= htmlspecialchars(substr($m['created_at'], 11, 5)) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <form method="POST" action="<?= $app_root ?>/company/chat.php?conv_id=<?= $active_conv_id ?>" class="chat-input-bar">
            <input type="text" name="message_text" class="form-control" placeholder="Type a message to <?= htmlspecialchars($active_student['student_name']) ?>..." required autocomplete="off">
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.25rem;">
              Send &rarr;
            </button>
          </form>
        <?php else: ?>
          <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--slate-400);">
            Select a candidate from the left list to review chat history.
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script>
window.addEventListener('load', () => {
  const box = document.getElementById('chat-box');
  if (box) box.scrollTop = box.scrollHeight;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
