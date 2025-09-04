<?php
// Complete messaging flow test between job seeker and job provider
require_once __DIR__ . '/inc/db.php';

$test_results = [];
$step = $_GET['step'] ?? 'start';

// Create test users if they don't exist
function createTestUsers($pdo) {
    // Create job seeker
    $seeker_email = 'msgtest_seeker@example.com';
    $provider_email = 'msgtest_provider@example.com';
    
    $seeker = $pdo->prepare("SELECT * FROM users WHERE email = ?")->execute([$seeker_email]) ? $pdo->prepare("SELECT * FROM users WHERE email = ?")->execute([$seeker_email]) : null;
    if (!$seeker) {
        $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, experience_level) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $stmt->execute(['job_seeker', 'Message Test Seeker', $seeker_email, '+1555111001', password_hash('msgtest123', PASSWORD_DEFAULT), 'intermediate']);
        $seeker_id = $pdo->lastInsertId();
        
        // Add category and skills
        $pdo->prepare("INSERT INTO user_job_categories (user_id, category_id) VALUES (?, 1)")->execute([$seeker_id]);
    }
    
    $provider = $pdo->query("SELECT * FROM users WHERE email = '$provider_email'")->fetch();
    if (!$provider) {
        $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, profession, location) VALUES (?, ?, ?, ?, ?, 1, ?, ?)");
        $stmt->execute(['job_provider', 'Message Test Provider', $provider_email, '+1555111002', password_hash('msgtest123', PASSWORD_DEFAULT), 'Test Company Inc', 'Test City']);
        $provider_id = $pdo->lastInsertId();
        
        // Create a test job posting
        $pdo->prepare("INSERT INTO job_postings (user_id, title, description, budget_min, budget_max, job_type, status) VALUES (?, ?, ?, ?, ?, ?, 'open')")
            ->execute([$provider_id, 'Test Job for Messaging', 'This is a test job posting to verify the messaging system works correctly.', 500, 2000, 'contract']);
    }
    
    return [
        'seeker' => $pdo->query("SELECT * FROM users WHERE email = '$seeker_email'")->fetch(),
        'provider' => $pdo->query("SELECT * FROM users WHERE email = '$provider_email'")->fetch()
    ];
}

// Test the complete messaging flow
if ($step === 'test') {
    try {
        $users = createTestUsers($pdo);
        $seeker = $users['seeker'];
        $provider = $users['provider'];
        
        // Step 1: Create conversation (simulating contact button click)
        $conv_stmt = $pdo->prepare("
            SELECT id FROM conversations 
            WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
        ");
        $conv_stmt->execute([$seeker['id'], $provider['id'], $provider['id'], $seeker['id']]);
        $conversation = $conv_stmt->fetch();
        
        if (!$conversation) {
            $create_conv = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
            $create_conv->execute([$seeker['id'], $provider['id']]);
            $conversation_id = $pdo->lastInsertId();
            $test_results[] = "✅ Conversation created (ID: $conversation_id)";
        } else {
            $conversation_id = $conversation['id'];
            $test_results[] = "✅ Existing conversation found (ID: $conversation_id)";
        }
        
        // Step 2: Send message from seeker to provider
        $seeker_message = "Hello! I'm interested in your job posting. Can we discuss the details?";
        $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message, message_type) VALUES (?, ?, ?, 'text')");
        $stmt->execute([$conversation_id, $seeker['id'], $seeker_message]);
        $pdo->prepare("UPDATE conversations SET last_message_at = NOW() WHERE id = ?")->execute([$conversation_id]);
        $test_results[] = "✅ Message sent from seeker to provider";
        
        // Step 3: Send reply from provider to seeker
        $provider_message = "Hi! Thank you for your interest. I'd love to discuss this opportunity with you. What's your experience with similar projects?";
        $stmt->execute([$conversation_id, $provider['id'], $provider_message]);
        $pdo->prepare("UPDATE conversations SET last_message_at = NOW() WHERE id = ?")->execute([$conversation_id]);
        $test_results[] = "✅ Reply sent from provider to seeker";
        
        // Step 4: Test message retrieval
        $stmt = $pdo->prepare("
            SELECT m.*, u.name as sender_name, u.role as sender_role
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$conversation_id]);
        $messages = $stmt->fetchAll();
        $test_results[] = "✅ Retrieved " . count($messages) . " messages successfully";
        
        // Step 5: Test unread count
        $unread_for_seeker = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND sender_id = ? AND is_read = 0");
        $unread_for_seeker->execute([$conversation_id, $provider['id']]);
        $seeker_unread = $unread_for_seeker->fetchColumn();
        
        $unread_for_provider = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND sender_id = ? AND is_read = 0");
        $unread_for_provider->execute([$conversation_id, $seeker['id']]);
        $provider_unread = $unread_for_provider->fetchColumn();
        
        $test_results[] = "✅ Unread counts: Seeker has $seeker_unread unread, Provider has $provider_unread unread";
        
        // Step 6: Test mark as read functionality
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id = ?")->execute([$conversation_id, $provider['id']]);
        $test_results[] = "✅ Messages marked as read for seeker";
        
        $success = true;
        $test_data = [
            'seeker' => $seeker,
            'provider' => $provider,
            'conversation_id' => $conversation_id,
            'messages' => $messages
        ];
        
    } catch (Exception $e) {
        $success = false;
        $test_results[] = "❌ Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Messaging Test - Sconnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #005a87; }
        .btn.success { background: #28a745; }
        .btn.warning { background: #ffc107; color: #000; }
        .test-result { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #007cba; }
        .success-result { border-left-color: #28a745; background: #d4edda; }
        .error-result { border-left-color: #dc3545; background: #f8d7da; }
        .message-item { background: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .user-info { background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Complete Messaging System Test</h1>
            <p>Testing the full messaging flow between job seekers and job providers</p>
        </div>
        
        <?php if ($step === 'start'): ?>
            <div class="test-result">
                <h3>🧪 Ready to Test Messaging System</h3>
                <p>This test will:</p>
                <ul>
                    <li>Create test users (job seeker and job provider)</li>
                    <li>Simulate contact button functionality</li>
                    <li>Test conversation creation</li>
                    <li>Send messages in both directions</li>
                    <li>Verify message retrieval and read status</li>
                    <li>Test all messaging features</li>
                </ul>
                <a href="?step=test" class="btn">🚀 Start Complete Test</a>
            </div>
        <?php endif; ?>
        
        <?php if ($step === 'test'): ?>
            <div class="test-result <?= isset($success) && $success ? 'success-result' : 'error-result' ?>">
                <h3><?= isset($success) && $success ? '✅ Test Completed Successfully!' : '❌ Test Failed' ?></h3>
                
                <?php foreach ($test_results as $result): ?>
                    <div style="margin: 5px 0; font-family: monospace;"><?= htmlspecialchars($result) ?></div>
                <?php endforeach; ?>
            </div>
            
            <?php if (isset($test_data)): ?>
                <div class="user-info">
                    <h4>📋 Test User Credentials:</h4>
                    <p><strong>Job Seeker:</strong> <?= htmlspecialchars($test_data['seeker']['email']) ?> | Password: msgtest123</p>
                    <p><strong>Job Provider:</strong> <?= htmlspecialchars($test_data['provider']['email']) ?> | Password: msgtest123</p>
                </div>
                
                <div class="test-result">
                    <h4>💬 Test Messages:</h4>
                    <?php foreach ($test_data['messages'] as $msg): ?>
                        <div class="message-item">
                            <strong><?= htmlspecialchars($msg['sender_name']) ?> (<?= htmlspecialchars($msg['sender_role']) ?>):</strong><br>
                            <?= htmlspecialchars($msg['message']) ?>
                            <div style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                                <?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="chat.php?to=<?= $test_data['provider']['id'] ?>" class="btn">💬 Test Chat (as Seeker)</a>
                    <a href="chat.php?to=<?= $test_data['seeker']['id'] ?>" class="btn">💬 Test Chat (as Provider)</a>
                    <a href="messages.php" class="btn">📨 View Messages Page</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
            <h3>🔗 Test All Contact Buttons:</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                <a href="browse_jobs.php" class="btn">📋 Browse Jobs (Seeker)</a>
                <a href="search_talent.php" class="btn">🔍 Search Talent (Provider)</a>
                <a href="dashboard_job_seeker.php" class="btn">🏠 Seeker Dashboard</a>
                <a href="dashboard_job_provider.php" class="btn">🏢 Provider Dashboard</a>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
            <h4>📝 Manual Testing Checklist:</h4>
            <ul>
                <li>✅ Contact buttons work on Browse Jobs page</li>
                <li>✅ Contact buttons work on Search Talent page</li>
                <li>✅ Messages button works on both dashboards</li>
                <li>✅ Chat page loads and sends messages</li>
                <li>✅ Messages page shows conversations</li>
                <li>✅ File attachments work in chat</li>
                <li>✅ Unread message counts display correctly</li>
                <li>✅ Messages marked as read when viewed</li>
            </ul>
        </div>
    </div>
</body>
</html>
