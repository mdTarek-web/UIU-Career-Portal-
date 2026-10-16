<?php
/**
 * UIU Career Portal - Student: Recruiter Chat System
 * Direct, verified communication between shortlisted candidates and corporate hiring leads
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['student', 'admin']);
$app_root = get_app_root();
$active_role_section = 'student';
$page_title = 'Recruiter Messages | UIU Career Portal';

$user = current_user();
$pdo = get_db_connection();

$student_id = $user['profile']['id'] ?? 1;
$conversations = [];
$active_conv_id = (int)($_GET['conv_id'] ?? 0);
$messages = [];
$active_company = null;

if ($pdo) {
    // 1. Fetch conversations for this student
    try {
        $cStmt = $pdo->prepare("
            SELECT c.*, comp.name as company_name, comp.sector as company_sector, comp.logo as company_logo 
            FROM conversations c
            JOIN companies comp ON c.company_id = comp.id
            WHERE c.student_id = ?
            ORDER BY c.last_message_at DESC
        ");
        $cStmt->execute([$student_id]);
        $conversations = $cStmt->fetchAll();

        if (empty($active_conv_id) && !empty($conversations)) {
            $active_conv_id = (int)$conversations[0]['id'];
        }

        // 2. Handle new message post
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text']) && $active_conv_id > 0) {
            $text = trim($_POST['message_text']);
            if (!empty($text)) {
                $senderName = $user['name'] ?? 'Mugdha Rahman';
                $mStmt = $pdo->prepare("
                    INSERT INTO messages (conversation_id, sender_type, sender_name, message_text)
                    VALUES (?, 'student', ?, ?)
                ");
                $mStmt->execute([$active_conv_id, $senderName, $text]);

                // Update conversation last message
                $uStmt = $pdo->prepare("UPDATE conversations SET last_message = ?, last_message_at = NOW() WHERE id = ?");
                $uStmt->execute([$text, $active_conv_id]);

                // If AJAX request, return JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    json_response(['success' => true, 'text' => $text, 'time' => date('h:i A')]);
                }
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
                    $active_company = $c;
                    break;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error in student/chat.php: " . $e->getMessage());
    }
}

// Fallback demo conversation
if (empty($conversations)) {
    $conversations = [
        [
            'id' => 1,
            'company_name' => 'TechFlow Solutions',
            'company_sector' => 'Software & IT',
            'last_message' => 'We look forward to meeting you on Thursday at 2:00 PM.',
            'last_message_at' => date('Y-m-d H:i:s')
        ]
    ];
    $active_conv_id = 1;
    $active_company = $conversations[0];
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
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #fed7aa;">Student Portal</div>
        <div style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin-top: 0.2rem;">Mugdha Rahman</div>
        <div style="font-size: 0.75rem; color: #94a3b8;">Recruiter Chat</div>
      </div>
      <ul class="sidebar-menu">
        <li><a href="<?= $app_root ?>/student/dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
        <li><a href="<?= $app_root ?>/student/jobs.php" class="sidebar-link">💼 Browse Jobs & Internships</a></li>
        <li><a href="<?= $app_root ?>/student/applications.php" class="sidebar-link">📑 My Applications</a></li>
        <li><a href="<?= $app_root ?>/student/profile.php" class="sidebar-link">👤 Student Profile</a></li>
        <li><a href="<?= $app_root ?>/student/resume_builder.php" class="sidebar-link">📄 ATS Resume Builder</a></li>
        <li><a href="<?= $app_root ?>/student/chat.php" class="sidebar-link active">💬 Recruiter Messages</a></li>
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
        <h1 class="topbar-title">Direct Recruiter Communications</h1>
        <p style="color: var(--slate-600); font-size: 0.875rem;">Verified communication channel with company hiring managers</p>
      </div>
    </div>

    <!-- Chat Container -->
    <div class="chat-container">
      
      <!-- Thread List -->
      <div class="chat-sidebar">
        <div style="padding: 1.25rem; border-bottom: 1px solid var(--slate-200); font-weight: 800; font-size: 0.875rem; color: var(--slate-700);">
          Active Conversations (<?= count($conversations) ?>)
        </div>
        <div style="overflow-y: auto; flex: 1;">
          <?php foreach ($conversations as $c): ?>
            <a href="<?= $app_root ?>/student/chat.php?conv_id=<?= $c['id'] ?>" class="chat-thread <?= ($c['id'] == $active_conv_id) ? 'active' : '' ?>" style="text-decoration: none; display: block; color: inherit;">
              <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.25rem;">
                <span style="font-weight: 800; font-size: 0.95rem; color: var(--slate-900);"><?= htmlspecialchars($c['company_name']) ?></span>
                <span style="font-size: 0.7rem; color: var(--slate-400);">Active</span>
              </div>
              <div style="font-size: 0.8125rem; color: var(--primary); font-weight: 700; margin-bottom: 0.25rem;">
                <?= htmlspecialchars($c['company_sector'] ?? 'Partner Employer') ?>
              </div>
              <p style="font-size: 0.75rem; color: var(--slate-500); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <?= htmlspecialchars($c['last_message'] ?? 'Click to start conversation') ?>
              </p>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Messages Pane -->
      <div class="chat-main">
        <?php if ($active_company): ?>
          <div class="chat-header">
            <div>
              <div style="font-weight: 900; font-size: 1.1rem; color: var(--slate-900);"><?= htmlspecialchars($active_company['company_name']) ?></div>
              <div style="font-size: 0.75rem; color: #10b981; font-weight: 700;">● Online • Verified Corporate Partner</div>
            </div>
            <a href="<?= $app_root ?>/student/jobs.php" class="btn btn-outline btn-sm">Company Jobs &rarr;</a>
          </div>

          <div class="chat-messages" id="chat-messages-box">
            <?php foreach ($messages as $m): ?>
              <?php $isMe = ($m['sender_type'] === 'student'); ?>
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

          <form method="POST" action="<?= $app_root ?>/student/chat.php?conv_id=<?= $active_conv_id ?>" class="chat-input-bar" id="chat-form">
            <input type="text" name="message_text" id="chat-input" class="form-control" placeholder="Type your response to the recruiter..." required autocomplete="off">
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.25rem;">
              Send &rarr;
            </button>
          </form>
        <?php else: ?>
          <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--slate-400);">
            Select a conversation on the left to start chatting.
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script>
// Auto scroll to bottom of chat
window.addEventListener('load', () => {
  const box = document.getElementById('chat-messages-box');
  if (box) box.scrollTop = box.scrollHeight;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
