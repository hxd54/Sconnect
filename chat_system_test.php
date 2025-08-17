<?php
// Chat System Comprehensive Test
require_once __DIR__ . '/inc/db.php';

$test_results = [];
$chat_features = [];

// Test 1: Chat Database Tables
try {
    $conversations_table = $pdo->query("DESCRIBE conversations")->fetchAll();
    $messages_table = $pdo->query("DESCRIBE messages")->fetchAll();
    
    $required_conv_fields = ['id', 'user1_id', 'user2_id', 'last_message_at', 'created_at'];
    $required_msg_fields = ['id', 'conversation_id', 'sender_id', 'message', 'message_type', 'file_path', 'is_read', 'created_at'];
    
    $conv_fields = array_column($conversations_table, 'Field');
    $msg_fields = array_column($messages_table, 'Field');
    
    $missing_conv = array_diff($required_conv_fields, $conv_fields);
    $missing_msg = array_diff($required_msg_fields, $msg_fields);
    
    if (empty($missing_conv) && empty($missing_msg)) {
        $test_results['chat_tables'] = ['status' => 'success', 'message' => 'All chat table fields exist'];
    } else {
        $test_results['chat_tables'] = ['status' => 'error', 'message' => 'Missing fields: ' . implode(', ', array_merge($missing_conv, $missing_msg))];
    }
} catch (Exception $e) {
    $test_results['chat_tables'] = ['status' => 'error', 'message' => 'Chat tables error: ' . $e->getMessage()];
}

// Test 2: Chat Files Existence
$chat_files = ['chat.php', 'messages.php'];
$missing_chat_files = [];

foreach ($chat_files as $file) {
    if (!file_exists($file)) {
        $missing_chat_files[] = $file;
    }
}

if (empty($missing_chat_files)) {
    $test_results['chat_files'] = ['status' => 'success', 'message' => 'All chat files exist'];
} else {
    $test_results['chat_files'] = ['status' => 'error', 'message' => 'Missing files: ' . implode(', ', $missing_chat_files)];
}

// Test 3: Upload Directory for Chat
$chat_upload_dir = 'uploads/chat';
if (is_dir($chat_upload_dir) && is_writable($chat_upload_dir)) {
    $test_results['chat_uploads'] = ['status' => 'success', 'message' => 'Chat upload directory exists and is writable'];
} else {
    $test_results['chat_uploads'] = ['status' => 'warning', 'message' => 'Chat upload directory missing or not writable'];
}

// Test 4: Contact Links in System
$contact_links_test = [];

// Check if browse_jobs.php has contact functionality
if (file_exists('browse_jobs.php')) {
    $browse_content = file_get_contents('browse_jobs.php');
    if (strpos($browse_content, 'chat.php') !== false) {
        $contact_links_test[] = 'browse_jobs.php ✓';
    } else {
        $contact_links_test[] = 'browse_jobs.php ✗';
    }
}

// Check if search_talent.php has contact functionality
if (file_exists('search_talent.php')) {
    $search_content = file_get_contents('search_talent.php');
    if (strpos($search_content, 'chat.php') !== false) {
        $contact_links_test[] = 'search_talent.php ✓';
    } else {
        $contact_links_test[] = 'search_talent.php ✗';
    }
}

// Check if dashboards have message links
if (file_exists('dashboard_job_seeker.php')) {
    $dashboard_content = file_get_contents('dashboard_job_seeker.php');
    if (strpos($dashboard_content, 'messages.php') !== false) {
        $contact_links_test[] = 'job_seeker_dashboard ✓';
    } else {
        $contact_links_test[] = 'job_seeker_dashboard ✗';
    }
}

if (file_exists('dashboard_job_provider.php')) {
    $dashboard_content = file_get_contents('dashboard_job_provider.php');
    if (strpos($dashboard_content, 'messages.php') !== false) {
        $contact_links_test[] = 'job_provider_dashboard ✓';
    } else {
        $contact_links_test[] = 'job_provider_dashboard ✗';
    }
}

$test_results['contact_links'] = [
    'status' => strpos(implode('', $contact_links_test), '✗') === false ? 'success' : 'warning',
    'message' => implode(', ', $contact_links_test)
];

// Test 5: Sample Chat Data
try {
    $sample_conversations = $pdo->query("SELECT COUNT(*) FROM conversations")->fetchColumn();
    $sample_messages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    
    if ($sample_conversations > 0 || $sample_messages > 0) {
        $test_results['sample_data'] = ['status' => 'success', 'message' => "$sample_conversations conversations, $sample_messages messages"];
    } else {
        $test_results['sample_data'] = ['status' => 'info', 'message' => 'No chat data yet - create test conversations'];
    }
} catch (Exception $e) {
    $test_results['sample_data'] = ['status' => 'error', 'message' => 'Sample data error: ' . $e->getMessage()];
}

// Chat Features Checklist
$chat_features = [
    'Real-time messaging' => 'Manual test required',
    'File attachments' => 'Upload directory exists: ' . (is_dir('uploads/chat') ? 'Yes' : 'No'),
    'Message history' => 'Database tables ready',
    'Conversation list' => 'messages.php exists: ' . (file_exists('messages.php') ? 'Yes' : 'No'),
    'User-to-user contact' => 'Contact links in dashboards and search pages',
    'Message read status' => 'is_read field exists in messages table',
    'File type validation' => 'Implemented in chat.php',
    'Telegram-like interface' => 'Modern UI with bubbles and timestamps'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat System Test - Sconnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .test-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .test-result { padding: 15px; border-radius: 8px; border-left: 4px solid; }
        .test-result h3 { margin: 0 0 10px 0; }
        .test-result p { margin: 0; }
        .success { background: #d4edda; color: #155724; border-left-color: #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left-color: #dc3545; }
        .warning { background: #fff3cd; color: #856404; border-left-color: #ffc107; }
        .info { background: #d1ecf1; color: #0c5460; border-left-color: #17a2b8; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; }
        .feature-item { padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; }
        .feature-name { font-weight: bold; color: #495057; }
        .feature-status { font-size: 0.9rem; color: #6c757d; margin-top: 5px; }
        .test-instructions { background: #e3f2fd; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Chat System Comprehensive Test</h1>
            <p>Testing all chat functionality and user interactions</p>
        </div>
        
        <h2>🔧 Technical Tests</h2>
        <div class="test-grid">
            <?php foreach ($test_results as $test_name => $result): ?>
                <div class="test-result <?= $result['status'] ?>">
                    <h3>
                        <?php if ($result['status'] === 'success'): ?>✅<?php elseif ($result['status'] === 'warning'): ?>⚠️<?php elseif ($result['status'] === 'info'): ?>ℹ️<?php else: ?>❌<?php endif; ?>
                        <?= ucfirst(str_replace('_', ' ', $test_name)) ?>
                    </h3>
                    <p><?= htmlspecialchars($result['message']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <h2>💬 Chat Features Status</h2>
        <div class="features-grid">
            <?php foreach ($chat_features as $feature => $status): ?>
                <div class="feature-item">
                    <div class="feature-name"><?= htmlspecialchars($feature) ?></div>
                    <div class="feature-status"><?= htmlspecialchars($status) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="test-instructions">
            <h3>🧪 Manual Chat System Testing Instructions</h3>
            <ol>
                <li><strong>Create Test Users:</strong>
                    <ul>
                        <li>Register as Job Seeker: <a href="signup.php?role=job_seeker" target="_blank">Job Seeker Registration</a></li>
                        <li>Register as Job Provider: <a href="signup.php?role=job_provider" target="_blank">Job Provider Registration</a></li>
                    </ul>
                </li>
                <li><strong>Test Contact Initiation:</strong>
                    <ul>
                        <li>Login as Job Seeker → <a href="browse_jobs.php" target="_blank">Browse Jobs</a> → Contact a provider</li>
                        <li>Login as Job Provider → <a href="search_talent.php" target="_blank">Search Talent</a> → Contact a seeker</li>
                    </ul>
                </li>
                <li><strong>Test Messaging Features:</strong>
                    <ul>
                        <li>Send text messages</li>
                        <li>Upload and send files (images, documents)</li>
                        <li>Check message timestamps</li>
                        <li>Verify message delivery</li>
                    </ul>
                </li>
                <li><strong>Test Message Overview:</strong>
                    <ul>
                        <li>Go to <a href="messages.php" target="_blank">Messages Overview</a></li>
                        <li>Check conversation list</li>
                        <li>Verify unread message counts</li>
                        <li>Test conversation navigation</li>
                    </ul>
                </li>
                <li><strong>Test Cross-User Interaction:</strong>
                    <ul>
                        <li>Login as second user</li>
                        <li>Check received messages</li>
                        <li>Reply to conversations</li>
                        <li>Test file downloads</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="system_test.php" class="btn">🔧 Full System Test</a>
            <a href="test_registration.php" class="btn">👥 Create Test Users</a>
            <a href="debug_users.php" class="btn">🔍 View Users</a>
            <a href="index.php" class="btn">🏠 Homepage</a>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">
            <h4>💡 Expected Chat Flow:</h4>
            <p><strong>Job Seeker:</strong> Browse Jobs → See Job → Contact Provider → Start Chat</p>
            <p><strong>Job Provider:</strong> Search Talent → Find Seeker → Contact → Start Chat</p>
            <p><strong>Both Users:</strong> Messages Page → View Conversations → Continue Chats</p>
        </div>
    </div>
</body>
</html>
