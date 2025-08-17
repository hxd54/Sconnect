<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
$to_id = $_GET['to'] ?? null;
if (!$to_id) { echo "No user selected."; exit; }
require_once __DIR__ . '/inc/db.php';

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
        if ($_FILES['attachment']['size'] <= 10*1024*1024) {
            $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','mp4','mp3'];
            if (in_array($ext, $allowed)) {
                $dest = 'uploads/chat/' . uniqid('msg_', true) . '.' . $ext;
                if (!is_dir('uploads/chat')) mkdir('uploads/chat', 0755, true);
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $file_path = $dest;
                    $image_exts = ['jpg','jpeg','png','gif'];
                    $message_type = in_array($ext, $image_exts) ? 'image' : 'file';
                }
            }
        }
    }

    if ($msg || $file_path) {
        $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message, message_type, file_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$conversation_id, $user['id'], $msg ?: '', $message_type, $file_path]);
        $pdo->prepare("UPDATE conversations SET last_message_at = NOW() WHERE id = ?")->execute([$conversation_id]);
    }

    header("Location: modern_chat.php?to=$to_id");
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
    /* Telegram-inspired Chat Design */
    * { 
      box-sizing: border-box; 
      margin: 0; 
      padding: 0; 
    }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background: #0f1419;
      margin: 0;
      height: 100vh;
      overflow: hidden;
    }
    
    .chat-container {
      display: flex;
      flex-direction: column;
      height: 100vh;
      max-width: 100%;
      margin: 0 auto;
      background: #17212b;
    }
    
    /* Telegram-style Header */
    .chat-header {
      background: #2b5278;
      color: white;
      padding: 1rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.3);
      position: relative;
      z-index: 100;
    }
    
    .back-btn {
      background: none;
      border: none;
      color: white;
      font-size: 1.2rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 50%;
      transition: all 0.2s ease;
    }
    
    .back-btn:hover {
      background: rgba(255,255,255,0.1);
    }
    
    .partner-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      font-size: 1rem;
    }
    
    .partner-info h3 {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.2rem;
    }
    
    .partner-status {
      font-size: 0.8rem;
      opacity: 0.8;
    }
    
    .chat-actions {
      margin-left: auto;
      display: flex;
      gap: 0.5rem;
    }
    
    .action-btn {
      background: none;
      border: none;
      color: white;
      font-size: 1.1rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 50%;
      transition: all 0.2s ease;
    }
    
    .action-btn:hover {
      background: rgba(255,255,255,0.1);
    }
    
    /* Messages Area */
    .messages-container {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
      background: #0f1419;
      background-image: 
        radial-gradient(circle at 25% 25%, #1a2332 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, #243447 0%, transparent 50%);
    }
    
    .message {
      display: flex;
      margin-bottom: 1rem;
      animation: messageSlide 0.3s ease-out;
    }
    
    @keyframes messageSlide {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .message.sent {
      justify-content: flex-end;
    }
    
    .message.received {
      justify-content: flex-start;
    }
    
    .message-bubble {
      max-width: 70%;
      padding: 0.75rem 1rem;
      border-radius: 18px;
      position: relative;
      word-wrap: break-word;
    }
    
    .message.sent .message-bubble {
      background: #2b5278;
      color: white;
      border-bottom-right-radius: 4px;
    }
    
    .message.received .message-bubble {
      background: #182533;
      color: #e1e5e9;
      border-bottom-left-radius: 4px;
    }
    
    .message-text {
      line-height: 1.4;
      font-size: 0.95rem;
    }
    
    .message-image {
      max-width: 100%;
      border-radius: 12px;
      margin-bottom: 0.5rem;
    }
    
    .message-file {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem;
      background: rgba(255,255,255,0.1);
      border-radius: 8px;
      margin-bottom: 0.5rem;
    }
    
    .message-time {
      font-size: 0.7rem;
      opacity: 0.7;
      margin-top: 0.3rem;
      text-align: right;
    }
    
    .message.received .message-time {
      text-align: left;
    }
    
    /* Input Area */
    .input-container {
      background: #17212b;
      padding: 1rem;
      border-top: 1px solid #2d3748;
    }
    
    .input-form {
      display: flex;
      gap: 0.5rem;
      align-items: flex-end;
    }
    
    .attach-btn {
      background: #2b5278;
      border: none;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    
    .attach-btn:hover {
      background: #3d6b9a;
      transform: scale(1.05);
    }
    
    .message-input {
      flex: 1;
      background: #182533;
      border: none;
      border-radius: 20px;
      padding: 0.75rem 1rem;
      color: white;
      font-size: 0.95rem;
      resize: none;
      max-height: 120px;
      min-height: 40px;
      font-family: inherit;
    }
    
    .message-input:focus {
      outline: none;
      background: #1e2a38;
    }
    
    .message-input::placeholder {
      color: #8b949e;
    }
    
    .send-btn {
      background: #2b5278;
      border: none;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    
    .send-btn:hover {
      background: #3d6b9a;
      transform: scale(1.05);
    }
    
    .send-btn:disabled {
      background: #4a5568;
      cursor: not-allowed;
      transform: none;
    }
    
    /* File input styling */
    .file-input {
      display: none;
    }
    
    /* Empty state */
    .empty-chat {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      color: #8b949e;
      text-align: center;
    }
    
    .empty-chat i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
    
    /* Mobile optimizations */
    @media (max-width: 768px) {
      .chat-container {
        height: 100vh;
      }
      
      .message-bubble {
        max-width: 85%;
      }
      
      .input-container {
        padding: 0.75rem;
      }
    }
    
    /* Desktop styles */
    @media (min-width: 769px) {
      .chat-container {
        max-width: 800px;
        height: 90vh;
        margin: 2rem auto;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      }
    }
  </style>
</head>
<body>
  <div class="chat-container">
    <!-- Telegram-style Header -->
    <div class="chat-header">
      <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div class="partner-avatar">
        <?= strtoupper(substr($partner['name'], 0, 2)) ?>
      </div>
      <div class="partner-info">
        <h3><?= htmlspecialchars($partner['name']) ?></h3>
        <div class="partner-status">
          <?= ucfirst($partner['role']) ?> •
          <?= $partner['location'] ? htmlspecialchars($partner['location']) : 'Online' ?>
        </div>
      </div>
      <div class="chat-actions">
        <button class="action-btn" title="Call">
          <i class="fas fa-phone"></i>
        </button>
        <button class="action-btn" title="Video call">
          <i class="fas fa-video"></i>
        </button>
        <button class="action-btn" title="More options">
          <i class="fas fa-ellipsis-v"></i>
        </button>
      </div>
    </div>

    <!-- Messages Area -->
    <div class="messages-container" id="messagesContainer">
      <?php if (empty($messages)): ?>
        <div class="empty-chat">
          <i class="fas fa-comments"></i>
          <h3>Start a conversation</h3>
          <p>Send a message to <?= htmlspecialchars($partner['name']) ?></p>
        </div>
      <?php else: ?>
        <?php foreach ($messages as $message): ?>
          <div class="message <?= $message['sender_id'] == $user['id'] ? 'sent' : 'received' ?>">
            <div class="message-bubble">
              <?php if ($message['message_type'] === 'image' && $message['file_path']): ?>
                <img src="<?= htmlspecialchars($message['file_path']) ?>" alt="Image" class="message-image">
              <?php endif; ?>

              <?php if ($message['message_type'] === 'file' && $message['file_path']): ?>
                <div class="message-file">
                  <i class="fas fa-file"></i>
                  <a href="<?= htmlspecialchars($message['file_path']) ?>" target="_blank" style="color: inherit; text-decoration: none;">
                    <?= basename($message['file_path']) ?>
                  </a>
                </div>
              <?php endif; ?>

              <?php if ($message['message']): ?>
                <div class="message-text"><?= nl2br(htmlspecialchars($message['message'])) ?></div>
              <?php endif; ?>

              <div class="message-time">
                <?= date('g:i A', strtotime($message['created_at'])) ?>
                <?php if ($message['sender_id'] == $user['id']): ?>
                  <i class="fas fa-check" style="margin-left: 0.3rem; opacity: 0.7;"></i>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Input Area -->
    <div class="input-container">
      <form method="post" enctype="multipart/form-data" class="input-form" id="messageForm">
        <button type="button" class="attach-btn" onclick="document.getElementById('fileInput').click()">
          <i class="fas fa-paperclip"></i>
        </button>

        <input type="file" id="fileInput" name="attachment" class="file-input"
               accept="image/*,.pdf,.doc,.docx,.mp4,.mp3" onchange="handleFileSelect(this)">

        <textarea name="message" class="message-input" placeholder="Type a message..."
                  id="messageInput" rows="1" onkeydown="handleKeyPress(event)"></textarea>

        <button type="submit" class="send-btn" id="sendBtn">
          <i class="fas fa-paper-plane"></i>
        </button>
      </form>
    </div>
  </div>

  <script>
    // Auto-resize textarea
    const messageInput = document.getElementById('messageInput');
    messageInput.addEventListener('input', function() {
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Handle Enter key
    function handleKeyPress(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('messageForm').submit();
      }
    }

    // Handle file selection
    function handleFileSelect(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2);

        if (file.size > 10 * 1024 * 1024) {
          alert('File too large. Maximum size is 10MB.');
          input.value = '';
          return;
        }

        // Show file preview in input
        messageInput.placeholder = `📎 ${fileName} (${fileSize}MB) - Press Enter to send`;
        messageInput.focus();
      }
    }

    // Auto-scroll to bottom
    function scrollToBottom() {
      const container = document.getElementById('messagesContainer');
      container.scrollTop = container.scrollHeight;
    }

    // Scroll to bottom on page load
    window.addEventListener('load', scrollToBottom);

    // Auto-refresh messages every 3 seconds
    setInterval(function() {
      if (document.visibilityState === 'visible') {
        fetch(window.location.href)
          .then(response => response.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMessages = doc.getElementById('messagesContainer').innerHTML;
            const currentMessages = document.getElementById('messagesContainer').innerHTML;

            if (newMessages !== currentMessages) {
              document.getElementById('messagesContainer').innerHTML = newMessages;
              scrollToBottom();
            }
          })
          .catch(error => console.log('Auto-refresh error:', error));
      }
    }, 3000);

    // Enable/disable send button based on input
    messageInput.addEventListener('input', function() {
      const sendBtn = document.getElementById('sendBtn');
      const hasText = this.value.trim().length > 0;
      const hasFile = document.getElementById('fileInput').files.length > 0;

      sendBtn.disabled = !hasText && !hasFile;
    });

    // Reset placeholder after sending
    document.getElementById('messageForm').addEventListener('submit', function() {
      setTimeout(() => {
        messageInput.placeholder = 'Type a message...';
        messageInput.style.height = 'auto';
      }, 100);
    });

    // Smooth scroll animation for new messages
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
          scrollToBottom();
        }
      });
    });

    observer.observe(document.getElementById('messagesContainer'), {
      childList: true,
      subtree: true
    });
  </script>
</body>
</html>
