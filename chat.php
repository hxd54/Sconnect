<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
$to_id = $_GET['to'] ?? null;
if (!$to_id) { echo "No user selected."; exit; }
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/components.php';

// Fetch chat partner info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$to_id]);
$partner = $stmt->fetch();
if (!$partner) { echo "User not found."; exit; }

// Find or create conversation
$conv_stmt = $pdo->prepare("
    SELECT id FROM conversations
    WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
");
$conv_stmt->execute([$user['id'], $to_id, $to_id, $user['id']]);
$conversation = $conv_stmt->fetch();

if (!$conversation) {
    // Create new conversation
    $create_conv = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
    $create_conv->execute([$user['id'], $to_id]);
    $conversation_id = $pdo->lastInsertId();
} else {
    $conversation_id = $conversation['id'];
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    $message_type = 'text';
    $file_path = null;

    // Handle file upload
    if (!empty($_FILES['attachment']['name'])) {
        $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        if ($_FILES['attachment']['size'] <= 10*1024*1024) { // 10MB limit
            $dangerous = ['exe','bat','cmd','com','pif','scr','vbs','js','jar','msi','dmg','app'];
            if (!in_array($ext, $dangerous, true)) {
                $dest = 'uploads/chat/' . uniqid('msg_', true) . '.' . $ext;
                if (!is_dir('uploads/chat')) mkdir('uploads/chat', 0755, true);
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $file_path = $dest;
                    $image_exts = ['jpg','jpeg','png','gif','webp'];
                    $message_type = in_array($ext, $image_exts, true) ? 'image' : 'file';
                }
            }
        }
    }

    if ($msg || $file_path) {
        $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message, message_type, file_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$conversation_id, $user['id'], $msg ?: '', $message_type, $file_path]);

        // Update conversation last message
        $update_conv = $pdo->prepare("UPDATE conversations SET last_message_at = NOW() WHERE id = ?");
        $update_conv->execute([$conversation_id]);
    }

    // Redirect to avoid resubmission
    header("Location: chat.php?to=$to_id");
    exit;
}

// Fetch chat history
$stmt = $pdo->prepare("
    SELECT m.*, u.name as sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$conversation_id]);
$messages = $stmt->fetchAll();

// Mark messages as read
$mark_read = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id = ?");
$mark_read->execute([$conversation_id, $to_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Chat with <?= htmlspecialchars($partner['name']) ?> – Sconnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      margin: 0;
      min-height: 100vh;
      padding: 1rem;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      height: 90vh;
      min-height: 600px;
      overflow: hidden;
    }
    .chat-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1.5rem 2rem;
      border-bottom: 1px solid #e2e8f0;
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      color: white;
      position: sticky;
      top: 0;
      z-index: 2;
    }
    .chat-header .avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(255,255,255,0.3);
    }
    .chat-header .info { flex: 1; }
    .chat-header .name {
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 0.2rem;
    }
    .chat-header .role {
      font-size: 0.9rem;
      opacity: 0.8;
    }
    .chat-header .back {
      color: white;
      text-decoration: none;
      font-size: 1.2rem;
      padding: 0.5rem;
      border-radius: 8px;
      transition: background 0.3s ease;
    }
    .chat-header .back:hover {
      background: rgba(255,255,255,0.1);
    }
    .chat-box {
      flex: 1;
      overflow-y: auto;
      background: #f8fafc;
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .msg {
      display: flex;
      flex-direction: column;
      margin-bottom: 1rem;
    }
    .msg.me { align-items: flex-end; }
    .msg.them { align-items: flex-start; }
    .msg .bubble {
      display: inline-block;
      padding: 1rem 1.5rem;
      border-radius: 20px;
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      color: #fff;
      max-width: 70%;
      word-break: break-word;
      font-size: 1rem;
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
      transition: all 0.3s ease;
      position: relative;
    }
    .msg.them .bubble {
      background: #fff;
      color: #1e293b;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .msg .bubble:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
    }
    .msg.them .bubble:hover {
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    .msg .meta {
      font-size: 0.8rem;
      color: #64748b;
      margin-top: 0.5rem;
      padding: 0 0.5rem;
    }
    .msg .file-attachment {
      margin-top: 0.5rem;
      padding: 0.8rem;
      background: rgba(255,255,255,0.1);
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.2);
    }
    .msg.them .file-attachment {
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
    }
    .msg .file-attachment a {
      color: inherit;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 500;
    }
    .msg .file-attachment a:hover {
      text-decoration: underline;
    }
    .msg .image-attachment {
      margin-top: 0.5rem;
    }
    .msg .image-attachment img {
      max-width: 100%;
      max-height: 300px;
      border-radius: 12px;
      object-fit: cover;
    }
    .msg.me .meta { text-align: right; }
    .msg.them .meta { text-align: left; }
    .msg .avatar { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; margin-right: 0.5em; vertical-align: middle; }
    .msg-row { display: flex; align-items: flex-end; gap: 0.5em; }
    .msg.me .msg-row { flex-direction: row-reverse; }
    .msg.them .msg-row { flex-direction: row; }
    .chat-form {
      display: flex;
      align-items: flex-end;
      gap: 1rem;
      padding: 1.5rem 2rem;
      border-top: 1px solid #e2e8f0;
      background: #fff;
      position: sticky;
      bottom: 0;
      z-index: 2;
    }
    .input-container {
      flex: 1;
      position: relative;
    }
    .chat-form textarea {
      width: 100%;
      min-height: 50px;
      max-height: 120px;
      padding: 1rem 3rem 1rem 1rem;
      border: 2px solid #e2e8f0;
      border-radius: 25px;
      font-size: 1rem;
      outline: none;
      transition: border-color 0.3s ease;
      resize: none;
      font-family: inherit;
    }
    .chat-form textarea:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .attachment-btn {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #64748b;
      cursor: pointer;
      font-size: 1.2rem;
      padding: 0.5rem;
      border-radius: 50%;
      transition: all 0.3s ease;
    }
    .attachment-btn:hover {
      background: #f1f5f9;
      color: #4f46e5;
    }
    .file-input {
      display: none;
    }
    .send-btn {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      color: #fff;
      border: none;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      cursor: pointer;
      font-size: 1.3rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
    }
    .send-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    }
    .send-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }
    .file-preview {
      margin-top: 0.5rem;
      padding: 0.5rem;
      background: #f1f5f9;
      border-radius: 8px;
      font-size: 0.9rem;
      color: #64748b;
      display: none;
    }
    .file-preview .remove-file {
      background: none;
      border: none;
      color: #ef4444;
      cursor: pointer;
      margin-left: 0.5rem;
    }
    .empty-chat {
      text-align: center;
      padding: 4rem 2rem;
      color: #64748b;
    }
    .empty-chat i {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: #cbd5e1;
    }
    .empty-chat h4 {
      font-size: 1.3rem;
      margin-bottom: 0.5rem;
      color: #475569;
    }
    .empty-chat p {
      font-size: 1rem;
    }
    @media (max-width: 768px) {
      .container { margin: 0; border-radius: 0; height: 100vh; }
      .chat-header { padding: 1rem 1.5rem; }
      .chat-box { padding: 1rem; }
      .chat-form { padding: 1rem 1.5rem; }
      .msg .bubble { max-width: 85%; }
    }
    @keyframes fadeInMsg { from { opacity: 0; transform: translateY(10px);} to { opacity: 1; transform: none;} }
    .msg { animation: fadeInMsg 0.3s; }
  </style>
</head>
<body>
  <div class="container">
    <div class="chat-header">
      <a href="javascript:history.back()" class="back">
        <i class="fas fa-arrow-left"></i>
      </a>
      <?= user_avatar($partner['id'], 50) ?>
      <div class="info">
        <div class="name"><?= htmlspecialchars($partner['name']) ?></div>
        <div class="role"><?= ucfirst(str_replace('_', ' ', $partner['role'])) ?></div>
      </div>
    </div>

    <div class="chat-box" id="chat-box">
      <?php if (!$messages): ?>
        <div class="empty-chat">
          <i class="fas fa-comments"></i>
          <h4>Start a conversation</h4>
          <p>Send a message to <?= htmlspecialchars($partner['name']) ?> to get started</p>
        </div>
      <?php else: ?>
        <?php foreach ($messages as $msg): ?>
          <div class="msg <?= $msg['sender_id'] == $user['id'] ? 'me' : 'them' ?>">
            <div class="bubble">
              <?php if ($msg['message']): ?>
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
              <?php endif; ?>

              <?php if ($msg['message_type'] === 'image' && $msg['file_path']): ?>
                <div class="image-attachment">
                  <img src="<?= htmlspecialchars($msg['file_path']) ?>" alt="Image" loading="lazy">
                </div>
              <?php elseif ($msg['message_type'] === 'file' && $msg['file_path']): ?>
                <div class="file-attachment">
                  <a href="<?= htmlspecialchars($msg['file_path']) ?>" target="_blank">
                    <i class="fas fa-file"></i>
                    <?= htmlspecialchars(basename($msg['file_path'])) ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>
            <div class="meta">
              <?= date('M j, g:i A', strtotime($msg['created_at'])) ?>
              <?php if ($msg['sender_id'] == $user['id']): ?>
                <?= $msg['is_read'] ? '<i class="fas fa-check-double" style="color: #10b981;"></i>' : '<i class="fas fa-check"></i>' ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <form method="post" enctype="multipart/form-data" class="chat-form" id="chat-form">
      <div class="input-container">
        <textarea
          name="message"
          id="message-input"
          placeholder="Type a message..."
          rows="1"
          onkeydown="handleKeyDown(event)"
          oninput="autoResize(this)"
        ></textarea>
        <button type="button" class="attachment-btn" onclick="document.getElementById('file-input').click()">
          <i class="fas fa-paperclip"></i>
        </button>
        <input type="file" id="file-input" name="attachment" class="file-input" onchange="showFilePreview(this)">
        <div class="file-preview" id="file-preview">
          <span id="file-name"></span>
          <button type="button" class="remove-file" onclick="removeFile()">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="send-btn" id="send-btn">
        <i class="fas fa-paper-plane"></i>
      </button>
    </form>
  </div>
  <script>
    // Auto-resize textarea
    function autoResize(textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';

      // Enable/disable send button
      const sendBtn = document.getElementById('send-btn');
      const hasText = textarea.value.trim().length > 0;
      const hasFile = document.getElementById('file-input').files.length > 0;
      sendBtn.disabled = !hasText && !hasFile;
    }

    // Handle Enter key
    function handleKeyDown(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        const form = document.getElementById('chat-form');
        const textarea = document.getElementById('message-input');
        const hasText = textarea.value.trim().length > 0;
        const hasFile = document.getElementById('file-input').files.length > 0;

        if (hasText || hasFile) {
          form.submit();
        }
      }
    }

    // File preview
    function showFilePreview(input) {
      const preview = document.getElementById('file-preview');
      const fileName = document.getElementById('file-name');

      if (input.files && input.files[0]) {
        const file = input.files[0];
        fileName.textContent = file.name;
        preview.style.display = 'block';

        // Enable send button
        document.getElementById('send-btn').disabled = false;
      }
    }

    function removeFile() {
      const input = document.getElementById('file-input');
      const preview = document.getElementById('file-preview');

      input.value = '';
      preview.style.display = 'none';

      // Check if send button should be disabled
      const textarea = document.getElementById('message-input');
      const hasText = textarea.value.trim().length > 0;
      document.getElementById('send-btn').disabled = !hasText;
    }

    // Scroll to bottom
    function scrollToBottom() {
      const chatBox = document.getElementById('chat-box');
      chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Auto-scroll on page load
    window.addEventListener('load', scrollToBottom);

    // Focus on message input
    document.getElementById('message-input').focus();

    // Initial button state
    document.getElementById('send-btn').disabled = true;
  </script>
</body>
</html>