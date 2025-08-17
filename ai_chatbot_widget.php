<?php
// AI Chatbot Widget - Universal component for all pages
// This widget integrates SmartPath AI into the SConnect platform
?>

<!-- AI Chatbot Widget -->
<div id="ai-chatbot-widget" class="ai-chatbot-widget">
  <!-- Chatbot Toggle Button -->
  <div id="chatbot-toggle" class="chatbot-toggle" onclick="toggleChatbot()">
    <i class="fas fa-robot"></i>
    <span class="chatbot-badge" id="chatbot-badge">AI</span>
  </div>

  <!-- Chatbot Window -->
  <div id="chatbot-window" class="chatbot-window">
    <!-- Header -->
    <div class="chatbot-header">
      <div class="chatbot-title">
        <i class="fas fa-robot"></i>
        <span>SmartPath AI Assistant</span>
      </div>
      <div class="chatbot-controls">
        <button class="chatbot-minimize" onclick="minimizeChatbot()">
          <i class="fas fa-minus"></i>
        </button>
        <button class="chatbot-close" onclick="closeChatbot()">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- Chat Messages -->
    <div class="chatbot-messages" id="chatbot-messages">
      <div class="chatbot-message ai-message">
        <div class="message-avatar">
          <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
          <p>Hello! I'm your AI career assistant. I can help you with:</p>
          <ul>
            <li>Job search advice</li>
            <li>CV improvement tips</li>
            <li>Career guidance</li>
            <li>Skill development</li>
            <li>Interview preparation</li>
          </ul>
          <p>How can I assist you today?</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="chatbot-quick-actions" id="chatbot-quick-actions">
      <button class="quick-action-btn" onclick="sendQuickMessage('What jobs am I qualified for?')">
        <i class="fas fa-briefcase"></i>
        Job Matching
      </button>
      <button class="quick-action-btn" onclick="sendQuickMessage('How can I improve my CV?')">
        <i class="fas fa-file-alt"></i>
        CV Tips
      </button>
      <button class="quick-action-btn" onclick="sendQuickMessage('What skills should I develop?')">
        <i class="fas fa-graduation-cap"></i>
        Skills
      </button>
      <button class="quick-action-btn" onclick="sendQuickMessage('Help me prepare for an interview')">
        <i class="fas fa-comments"></i>
        Interview
      </button>
    </div>

    <!-- Input Area -->
    <div class="chatbot-input-area">
      <div class="chatbot-input-container">
        <input type="text" id="chatbot-input" class="chatbot-input" 
               placeholder="Ask me anything about your career..." 
               onkeypress="handleChatbotKeypress(event)">
        <button class="chatbot-send-btn" onclick="sendChatbotMessage()">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
      <div class="chatbot-typing" id="chatbot-typing" style="display: none;">
        <span>AI is typing</span>
        <div class="typing-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* AI Chatbot Widget Styles */
.ai-chatbot-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 10000;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.chatbot-toggle {
  width: 60px;
  height: 60px;
  background: linear-gradient(45deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
  transition: all 0.3s ease;
  position: relative;
}

.chatbot-toggle:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
}

.chatbot-toggle i {
  color: white;
  font-size: 1.5rem;
}

.chatbot-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff4757;
  color: white;
  border-radius: 10px;
  padding: 2px 6px;
  font-size: 0.7rem;
  font-weight: bold;
}

.chatbot-window {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 380px;
  height: 500px;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  display: none;
  flex-direction: column;
  overflow: hidden;
  animation: chatbotSlideIn 0.3s ease;
}

@keyframes chatbotSlideIn {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.chatbot-header {
  background: linear-gradient(45deg, #667eea, #764ba2);
  color: white;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chatbot-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.chatbot-controls {
  display: flex;
  gap: 0.5rem;
}

.chatbot-minimize, .chatbot-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s ease;
}

.chatbot-minimize:hover, .chatbot-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.chatbot-messages {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
  max-height: 300px;
}

.chatbot-message {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
  animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ai-message .message-avatar {
  background: linear-gradient(45deg, #667eea, #764ba2);
  color: white;
}

.user-message .message-avatar {
  background: #e3f2fd;
  color: #1976d2;
}

.message-content {
  flex: 1;
  background: #f5f5f5;
  padding: 0.75rem;
  border-radius: 12px;
  font-size: 0.9rem;
  line-height: 1.4;
}

.ai-message .message-content {
  background: linear-gradient(135deg, #f0f4ff, #e8f2ff);
  border: 1px solid rgba(102, 126, 234, 0.1);
}

.user-message .message-content {
  background: linear-gradient(135deg, #e3f2fd, #bbdefb);
  border: 1px solid rgba(25, 118, 210, 0.1);
}

.chatbot-quick-actions {
  padding: 0.75rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.quick-action-btn {
  background: rgba(102, 126, 234, 0.1);
  border: 1px solid rgba(102, 126, 234, 0.2);
  border-radius: 8px;
  padding: 0.5rem;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #667eea;
}

.quick-action-btn:hover {
  background: rgba(102, 126, 234, 0.2);
  transform: translateY(-1px);
}

.chatbot-input-area {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding: 1rem;
}

.chatbot-input-container {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.chatbot-input {
  flex: 1;
  border: 2px solid #e5e7eb;
  border-radius: 25px;
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s ease;
}

.chatbot-input:focus {
  border-color: #667eea;
}

.chatbot-send-btn {
  background: linear-gradient(45deg, #667eea, #764ba2);
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
}

.chatbot-send-btn:hover {
  transform: scale(1.1);
}

.chatbot-typing {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
  font-size: 0.8rem;
  color: #8e8e8e;
}

.typing-dots {
  display: flex;
  gap: 2px;
}

.typing-dots span {
  width: 4px;
  height: 4px;
  background: #8e8e8e;
  border-radius: 50%;
  animation: typingDots 1.4s infinite;
}

.typing-dots span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typingDots {
  0%, 60%, 100% {
    transform: translateY(0);
  }
  30% {
    transform: translateY(-10px);
  }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .chatbot-window {
    width: 320px;
    height: 450px;
  }
  
  .chatbot-quick-actions {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .ai-chatbot-widget {
    bottom: 10px;
    right: 10px;
  }

  .chatbot-window {
    width: calc(100vw - 40px);
    height: 400px;
    right: -10px;
  }
}
</style>

<script>
// AI Chatbot JavaScript Functionality
let chatbotOpen = false;
let chatbotMinimized = false;
let chatSession = null;

// Initialize chatbot
document.addEventListener('DOMContentLoaded', function() {
  initializeChatbot();
});

function initializeChatbot() {
  // Create unique session ID
  chatSession = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

  // Add welcome message after a short delay
  setTimeout(() => {
    showWelcomeMessage();
  }, 1000);
}

function toggleChatbot() {
  const chatbotWindow = document.getElementById('chatbot-window');
  const chatbotToggle = document.getElementById('chatbot-toggle');

  if (chatbotOpen) {
    closeChatbot();
  } else {
    openChatbot();
  }
}

function openChatbot() {
  const chatbotWindow = document.getElementById('chatbot-window');
  const chatbotToggle = document.getElementById('chatbot-toggle');

  chatbotWindow.style.display = 'flex';
  chatbotToggle.style.display = 'none';
  chatbotOpen = true;
  chatbotMinimized = false;

  // Focus on input
  setTimeout(() => {
    document.getElementById('chatbot-input').focus();
  }, 300);
}

function closeChatbot() {
  const chatbotWindow = document.getElementById('chatbot-window');
  const chatbotToggle = document.getElementById('chatbot-toggle');

  chatbotWindow.style.display = 'none';
  chatbotToggle.style.display = 'flex';
  chatbotOpen = false;
  chatbotMinimized = false;
}

function minimizeChatbot() {
  const chatbotWindow = document.getElementById('chatbot-window');
  const chatbotToggle = document.getElementById('chatbot-toggle');

  chatbotWindow.style.display = 'none';
  chatbotToggle.style.display = 'flex';
  chatbotOpen = false;
  chatbotMinimized = true;

  // Update badge to show minimized state
  document.getElementById('chatbot-badge').textContent = '💬';
}

function handleChatbotKeypress(event) {
  if (event.key === 'Enter') {
    sendChatbotMessage();
  }
}

function sendQuickMessage(message) {
  document.getElementById('chatbot-input').value = message;
  sendChatbotMessage();
}

function sendChatbotMessage() {
  const input = document.getElementById('chatbot-input');
  const message = input.value.trim();

  if (!message) return;

  // Clear input
  input.value = '';

  // Add user message to chat
  addMessageToChat(message, 'user');

  // Hide quick actions after first message
  const quickActions = document.getElementById('chatbot-quick-actions');
  if (quickActions.style.display !== 'none') {
    quickActions.style.display = 'none';
  }

  // Show typing indicator
  showTypingIndicator();

  // Send message to AI
  sendToAI(message);
}

function addMessageToChat(message, sender) {
  const messagesContainer = document.getElementById('chatbot-messages');
  const messageDiv = document.createElement('div');
  messageDiv.className = `chatbot-message ${sender}-message`;

  const avatarDiv = document.createElement('div');
  avatarDiv.className = 'message-avatar';
  avatarDiv.innerHTML = sender === 'ai' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';

  const contentDiv = document.createElement('div');
  contentDiv.className = 'message-content';

  if (sender === 'ai') {
    // Format AI response with HTML
    contentDiv.innerHTML = formatAIResponse(message);
  } else {
    contentDiv.textContent = message;
  }

  messageDiv.appendChild(avatarDiv);
  messageDiv.appendChild(contentDiv);
  messagesContainer.appendChild(messageDiv);

  // Scroll to bottom
  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function formatAIResponse(response) {
  // Convert markdown-like formatting to HTML
  let formatted = response
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n\n/g, '</p><p>')
    .replace(/\n/g, '<br>');

  // Wrap in paragraph tags
  if (!formatted.startsWith('<p>')) {
    formatted = '<p>' + formatted + '</p>';
  }

  return formatted;
}

function showTypingIndicator() {
  const typingDiv = document.getElementById('chatbot-typing');
  typingDiv.style.display = 'flex';
}

function hideTypingIndicator() {
  const typingDiv = document.getElementById('chatbot-typing');
  typingDiv.style.display = 'none';
}

function sendToAI(message) {
  // Prepare request data
  const requestData = {
    message: message,
    session_id: chatSession,
    user_name: getCurrentUserName(),
    timestamp: new Date().toISOString()
  };

  // Send to SmartPath AI backend
  fetch('ai_chat_handler.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(requestData)
  })
  .then(response => response.json())
  .then(data => {
    hideTypingIndicator();

    if (data.success) {
      addMessageToChat(data.ai_response, 'ai');
    } else {
      addMessageToChat('Sorry, I encountered an error. Please try again later.', 'ai');
    }
  })
  .catch(error => {
    hideTypingIndicator();
    console.error('Chatbot error:', error);
    addMessageToChat('Sorry, I\'m having trouble connecting right now. Please try again later.', 'ai');
  });
}

function getCurrentUserName() {
  // Try to get user name from PHP session or return default
  return '<?php echo isset($_SESSION["user_name"]) ? $_SESSION["user_name"] : "User"; ?>';
}

function showWelcomeMessage() {
  // Add a subtle notification that AI is ready
  const badge = document.getElementById('chatbot-badge');
  badge.style.animation = 'pulse 2s infinite';

  setTimeout(() => {
    badge.style.animation = '';
  }, 6000);
}

// Add pulse animation
const style = document.createElement('style');
style.textContent = `
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
  }
`;
document.head.appendChild(style);
</script>
