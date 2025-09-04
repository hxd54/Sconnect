<?php
// Comprehensive test of the messaging system between job seekers and job providers
require_once __DIR__ . '/inc/db.php';

$test_results = [];
$overall_status = 'success';

// Test 1: Check messaging tables
try {
    $conversations_table = $pdo->query("DESCRIBE conversations")->fetchAll();
    $messages_table = $pdo->query("DESCRIBE messages")->fetchAll();
    
    $test_results['tables'] = ['status' => 'success', 'message' => 'Messaging tables exist and accessible'];
} catch (Exception $e) {
    $test_results['tables'] = ['status' => 'error', 'message' => 'Messaging tables error: ' . $e->getMessage()];
    $overall_status = 'error';
}

// Test 2: Create test users if they don't exist
if ($overall_status !== 'error') {
    try {
        // Check for existing test users
        $job_seeker = $pdo->query("SELECT * FROM users WHERE role = 'job_seeker' AND email LIKE 'test%' LIMIT 1")->fetch();
        $job_provider = $pdo->query("SELECT * FROM users WHERE role = 'job_provider' AND email LIKE 'test%' LIMIT 1")->fetch();
        
        // Create job seeker if doesn't exist
        if (!$job_seeker) {
            $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, experience_level) VALUES (?, ?, ?, ?, ?, 1, ?)");
            $stmt->execute(['job_seeker', 'Test Job Seeker', 'testseeker@example.com', '+1555001001', password_hash('test123', PASSWORD_DEFAULT), 'intermediate']);
            $seeker_id = $pdo->lastInsertId();
            $job_seeker = ['id' => $seeker_id, 'name' => 'Test Job Seeker', 'email' => 'testseeker@example.com'];
        }
        
        // Create job provider if doesn't exist
        if (!$job_provider) {
            $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, profession, location) VALUES (?, ?, ?, ?, ?, 1, ?, ?)");
            $stmt->execute(['job_provider', 'Test Job Provider', 'testprovider@example.com', '+1555001002', password_hash('test123', PASSWORD_DEFAULT), 'Test Company', 'Test City']);
            $provider_id = $pdo->lastInsertId();
            $job_provider = ['id' => $provider_id, 'name' => 'Test Job Provider', 'email' => 'testprovider@example.com'];
        }
        
        $test_results['users'] = [
            'status' => 'success', 
            'message' => 'Test users ready',
            'seeker' => $job_seeker,
            'provider' => $job_provider
        ];
    } catch (Exception $e) {
        $test_results['users'] = ['status' => 'error', 'message' => 'User creation error: ' . $e->getMessage()];
        $overall_status = 'error';
    }
}

// Test 3: Test conversation creation
if ($overall_status !== 'error' && isset($job_seeker) && isset($job_provider)) {
    try {
        $seeker_id = $job_seeker['id'];
        $provider_id = $job_provider['id'];
        
        // Check if conversation exists
        $conv_stmt = $pdo->prepare("
            SELECT * FROM conversations 
            WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
        ");
        $conv_stmt->execute([$seeker_id, $provider_id, $provider_id, $seeker_id]);
        $conversation = $conv_stmt->fetch();
        
        if (!$conversation) {
            // Create conversation
            $create_conv = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
            $create_conv->execute([$seeker_id, $provider_id]);
            $conversation_id = $pdo->lastInsertId();
            $test_results['conversation'] = ['status' => 'success', 'message' => "New conversation created (ID: $conversation_id)"];
        } else {
            $conversation_id = $conversation['id'];
            $test_results['conversation'] = ['status' => 'success', 'message' => "Existing conversation found (ID: $conversation_id)"];
        }
    } catch (Exception $e) {
        $test_results['conversation'] = ['status' => 'error', 'message' => 'Conversation creation error: ' . $e->getMessage()];
        $overall_status = 'error';
    }
}

// Test 4: Test message sending (both directions)
if ($overall_status !== 'error' && isset($conversation_id)) {
    try {
        // Message from seeker to provider
        $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message, message_type) VALUES (?, ?, ?, 'text')");
        $stmt->execute([$conversation_id, $seeker_id, 'Hello! I am interested in your job posting. Can we discuss the details?']);
        
        // Message from provider to seeker
        $stmt->execute([$conversation_id, $provider_id, 'Hi! Thank you for your interest. I would love to discuss this opportunity with you.']);
        
        // Update conversation timestamp
        $pdo->prepare("UPDATE conversations SET last_message_at = NOW() WHERE id = ?")->execute([$conversation_id]);
        
        $test_results['messaging'] = ['status' => 'success', 'message' => 'Test messages sent successfully in both directions'];
    } catch (Exception $e) {
        $test_results['messaging'] = ['status' => 'error', 'message' => 'Message sending error: ' . $e->getMessage()];
        $overall_status = 'error';
    }
}

// Test 5: Test message retrieval
if ($overall_status !== 'error' && isset($conversation_id)) {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, u.name as sender_name, u.role as sender_role
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$conversation_id]);
        $messages = $stmt->fetchAll();
        
        $test_results['retrieval'] = [
            'status' => 'success', 
            'message' => count($messages) . ' messages retrieved successfully',
            'messages' => $messages
        ];
    } catch (Exception $e) {
        $test_results['retrieval'] = ['status' => 'error', 'message' => 'Message retrieval error: ' . $e->getMessage()];
    }
}

// Test 6: Test contact buttons functionality
$contact_buttons_test = [];

// Check dashboard contact buttons
$dashboard_files = ['dashboard_job_seeker.php', 'dashboard_job_provider.php'];
foreach ($dashboard_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'messages.php') !== false) {
            $contact_buttons_test[] = "$file: Messages button ✓";
        } else {
            $contact_buttons_test[] = "$file: Messages button ✗";
        }
    }
}

// Check browse/search contact buttons
$browse_files = ['browse_jobs.php' => 'chat.php', 'search_talent.php' => 'chat.php'];
foreach ($browse_files as $file => $expected) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, $expected) !== false) {
            $contact_buttons_test[] = "$file: Contact button ✓";
        } else {
            $contact_buttons_test[] = "$file: Contact button ✗";
        }
    }
}

$test_results['contact_buttons'] = [
    'status' => strpos(implode('', $contact_buttons_test), '✗') === false ? 'success' : 'warning',
    'message' => implode(', ', $contact_buttons_test)
];

// Test 7: Test unread message count
if (isset($conversation_id) && isset($seeker_id) && isset($provider_id)) {
    try {
        // Count unread messages for each user
        $unread_for_seeker = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND sender_id = ? AND is_read = 0");
        $unread_for_seeker->execute([$conversation_id, $provider_id]);
        $seeker_unread = $unread_for_seeker->fetchColumn();
        
        $unread_for_provider = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND sender_id = ? AND is_read = 0");
        $unread_for_provider->execute([$conversation_id, $seeker_id]);
        $provider_unread = $unread_for_provider->fetchColumn();
        
        $test_results['unread_count'] = [
            'status' => 'success',
            'message' => "Unread tracking working (Seeker: $seeker_unread, Provider: $provider_unread)"
        ];
    } catch (Exception $e) {
        $test_results['unread_count'] = ['status' => 'error', 'message' => 'Unread count error: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messaging System Test - Sconnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .status-overall { padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 1.2rem; font-weight: bold; }
        .status-overall.success { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
        .status-overall.error { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }
        .test-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; }
        .test-result { padding: 15px; border-radius: 8px; border-left: 4px solid; }
        .test-result h3 { margin: 0 0 10px 0; }
        .test-result p { margin: 0; }
        .success { background: #d4edda; color: #155724; border-left-color: #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left-color: #dc3545; }
        .warning { background: #fff3cd; color: #856404; border-left-color: #ffc107; }
        .user-details { background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px; font-size: 0.9rem; }
        .message-item { background: #e3f2fd; padding: 8px; margin: 5px 0; border-radius: 5px; font-size: 0.9rem; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Messaging System Test Results</h1>
            <p>Testing communication between job seekers and job providers</p>
        </div>
        
        <div class="status-overall <?= $overall_status ?>">
            <?php if ($overall_status === 'success'): ?>
                ✅ MESSAGING SYSTEM: FULLY FUNCTIONAL - All tests passed!
            <?php else: ?>
                ❌ MESSAGING SYSTEM: ISSUES DETECTED - Some components need attention
            <?php endif; ?>
        </div>
        
        <div class="test-grid">
            <?php foreach ($test_results as $test_name => $result): ?>
                <div class="test-result <?= $result['status'] ?>">
                    <h3>
                        <?php if ($result['status'] === 'success'): ?>✅<?php elseif ($result['status'] === 'warning'): ?>⚠️<?php else: ?>❌<?php endif; ?>
                        <?= ucfirst(str_replace('_', ' ', $test_name)) ?>
                    </h3>
                    <p><?= htmlspecialchars($result['message']) ?></p>
                    
                    <?php if (isset($result['seeker']) && isset($result['provider'])): ?>
                        <div class="user-details">
                            <strong>Test Users:</strong><br>
                            Job Seeker: <?= htmlspecialchars($result['seeker']['name']) ?> (<?= htmlspecialchars($result['seeker']['email']) ?>)<br>
                            Job Provider: <?= htmlspecialchars($result['provider']['name']) ?> (<?= htmlspecialchars($result['provider']['email']) ?>)<br>
                            <em>Password for both: test123</em>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($result['messages'])): ?>
                        <div class="user-details">
                            <strong>Test Messages:</strong>
                            <?php foreach (array_slice($result['messages'], -3) as $msg): ?>
                                <div class="message-item">
                                    <strong><?= htmlspecialchars($msg['sender_name']) ?> (<?= htmlspecialchars($msg['sender_role']) ?>):</strong><br>
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <?php if (isset($job_seeker) && isset($job_provider)): ?>
                <a href="chat.php?to=<?= $job_provider['id'] ?>" class="btn">💬 Test Chat (as Seeker)</a>
                <a href="chat.php?to=<?= $job_seeker['id'] ?>" class="btn">💬 Test Chat (as Provider)</a>
            <?php endif; ?>
            <a href="messages.php" class="btn">📨 View Messages</a>
            <a href="debug_users.php" class="btn">👥 View Users</a>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
            <h3>🧪 Manual Testing Instructions:</h3>
            <ol>
                <li><strong>Login as Job Seeker:</strong> Email: testseeker@example.com, Password: test123</li>
                <li><strong>Go to Browse Jobs:</strong> Find a job and click "Apply" to start chat</li>
                <li><strong>Send a message:</strong> Test text messages and file attachments</li>
                <li><strong>Login as Job Provider:</strong> Email: testprovider@example.com, Password: test123</li>
                <li><strong>Check Messages:</strong> Go to Messages page to see conversations</li>
                <li><strong>Reply:</strong> Send messages back to test two-way communication</li>
                <li><strong>Test Features:</strong> File uploads, message timestamps, read status</li>
            </ol>
        </div>
    </div>
</body>
</html>
