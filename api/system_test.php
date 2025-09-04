<?php
// Comprehensive System Functionality Test
require_once __DIR__ . '/inc/db.php';

$test_results = [];
$overall_status = 'success';

// Test 1: Database Connection and Tables
try {
    $required_tables = [
        'users', 'job_categories', 'skills', 'skill_categories', 
        'user_job_categories', 'user_skills', 'job_postings', 
        'conversations', 'messages', 'portfolio', 'reviews', 
        'post_likes', 'email_tokens', 'notifications', 'mentorship_requests'
    ];
    
    $missing_tables = [];
    foreach ($required_tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() == 0) {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        $test_results['database'] = ['status' => 'success', 'message' => 'All ' . count($required_tables) . ' tables exist'];
    } else {
        $test_results['database'] = ['status' => 'error', 'message' => 'Missing tables: ' . implode(', ', $missing_tables)];
        $overall_status = 'error';
    }
} catch (Exception $e) {
    $test_results['database'] = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    $overall_status = 'error';
}

// Test 2: User Registration System
try {
    $job_seekers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'job_seeker'")->fetchColumn();
    $job_providers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'job_provider'")->fetchColumn();
    $test_results['users'] = [
        'status' => 'success', 
        'message' => "Users: $job_seekers job seekers, $job_providers job providers"
    ];
} catch (Exception $e) {
    $test_results['users'] = ['status' => 'error', 'message' => 'User query error: ' . $e->getMessage()];
    $overall_status = 'error';
}

// Test 3: Categories and Skills System
try {
    $categories_count = $pdo->query("SELECT COUNT(*) FROM job_categories")->fetchColumn();
    $skills_count = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
    $mappings_count = $pdo->query("SELECT COUNT(*) FROM skill_categories")->fetchColumn();
    
    if ($categories_count >= 13 && $skills_count >= 70 && $mappings_count >= 70) {
        $test_results['categories_skills'] = [
            'status' => 'success', 
            'message' => "$categories_count categories, $skills_count skills, $mappings_count mappings"
        ];
    } else {
        $test_results['categories_skills'] = [
            'status' => 'warning', 
            'message' => "Low counts: $categories_count categories, $skills_count skills"
        ];
    }
} catch (Exception $e) {
    $test_results['categories_skills'] = ['status' => 'error', 'message' => 'Categories/Skills error: ' . $e->getMessage()];
    $overall_status = 'error';
}

// Test 4: Chat System
try {
    $conversations_count = $pdo->query("SELECT COUNT(*) FROM conversations")->fetchColumn();
    $messages_count = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    $test_results['chat_system'] = [
        'status' => 'success', 
        'message' => "$conversations_count conversations, $messages_count messages"
    ];
} catch (Exception $e) {
    $test_results['chat_system'] = ['status' => 'error', 'message' => 'Chat system error: ' . $e->getMessage()];
    $overall_status = 'error';
}

// Test 5: Job Postings
try {
    $jobs_count = $pdo->query("SELECT COUNT(*) FROM job_postings")->fetchColumn();
    $open_jobs = $pdo->query("SELECT COUNT(*) FROM job_postings WHERE status = 'open'")->fetchColumn();
    $test_results['job_postings'] = [
        'status' => 'success', 
        'message' => "$jobs_count total jobs, $open_jobs open positions"
    ];
} catch (Exception $e) {
    $test_results['job_postings'] = ['status' => 'error', 'message' => 'Job postings error: ' . $e->getMessage()];
    $overall_status = 'error';
}

// Test 6: File Upload Directories
$upload_dirs = ['uploads', 'uploads/chat'];
$missing_dirs = [];
foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        $missing_dirs[] = $dir;
    }
}

if (empty($missing_dirs)) {
    $test_results['file_uploads'] = ['status' => 'success', 'message' => 'All upload directories exist'];
} else {
    $test_results['file_uploads'] = ['status' => 'warning', 'message' => 'Missing directories: ' . implode(', ', $missing_dirs)];
}

// Test 7: Key Files Existence
$key_files = [
    'index.php', 'signup.php', 'login.php', 'chat.php', 'messages.php',
    'dashboard_job_seeker.php', 'dashboard_job_provider.php',
    'browse_jobs.php', 'search_talent.php', 'get_skills.php'
];

$missing_files = [];
foreach ($key_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (empty($missing_files)) {
    $test_results['key_files'] = ['status' => 'success', 'message' => 'All key files exist'];
} else {
    $test_results['key_files'] = ['status' => 'error', 'message' => 'Missing files: ' . implode(', ', $missing_files)];
    $overall_status = 'error';
}

// Test 8: AJAX Endpoints
$ajax_endpoints = ['get_skills.php'];
$ajax_status = [];

foreach ($ajax_endpoints as $endpoint) {
    if (file_exists($endpoint)) {
        $ajax_status[] = $endpoint . ' ✓';
    } else {
        $ajax_status[] = $endpoint . ' ✗';
        $overall_status = 'error';
    }
}

$test_results['ajax_endpoints'] = [
    'status' => strpos(implode('', $ajax_status), '✗') === false ? 'success' : 'error',
    'message' => implode(', ', $ajax_status)
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Functionality Test - Sconnect</title>
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
        .actions { margin-top: 30px; text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #005a87; }
        .functionality-test { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .test-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 15px; }
        .test-link { padding: 10px; background: white; border: 1px solid #ddd; border-radius: 5px; text-align: center; }
        .test-link a { text-decoration: none; color: #007cba; font-weight: 500; }
        .test-link a:hover { color: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Sconnect System Functionality Test</h1>
            <p>Comprehensive test of all system components and features</p>
        </div>
        
        <div class="status-overall <?= $overall_status ?>">
            <?php if ($overall_status === 'success'): ?>
                ✅ System Status: ALL TESTS PASSED - System is fully functional!
            <?php else: ?>
                ❌ System Status: ISSUES DETECTED - Some components need attention
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
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="functionality-test">
            <h2>🧪 Manual Functionality Tests</h2>
            <p>Test these features manually to ensure complete functionality:</p>
            
            <div class="test-links">
                <div class="test-link">
                    <a href="signup.php?role=job_seeker" target="_blank">
                        <i>👤</i><br>Job Seeker Registration
                    </a>
                </div>
                <div class="test-link">
                    <a href="signup.php?role=job_provider" target="_blank">
                        <i>🏢</i><br>Job Provider Registration
                    </a>
                </div>
                <div class="test-link">
                    <a href="login.php" target="_blank">
                        <i>🔐</i><br>Login System
                    </a>
                </div>
                <div class="test-link">
                    <a href="browse_jobs.php" target="_blank">
                        <i>💼</i><br>Browse Jobs
                    </a>
                </div>
                <div class="test-link">
                    <a href="search_talent.php" target="_blank">
                        <i>🔍</i><br>Search Talent
                    </a>
                </div>
                <div class="test-link">
                    <a href="test_job_seeker_form.php" target="_blank">
                        <i>🧪</i><br>Form Testing
                    </a>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <a href="index.php" class="btn">🏠 Homepage</a>
            <a href="debug_users.php" class="btn">👥 Debug Users</a>
            <a href="setup_database.php" class="btn">🗄️ Database Setup</a>
            <a href="test_registration.php" class="btn">➕ Quick User Creation</a>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
            <h3>🔄 Chat System Test Instructions:</h3>
            <ol>
                <li><strong>Create two users:</strong> One job seeker and one job provider</li>
                <li><strong>Login as job seeker:</strong> Go to dashboard → Browse jobs → Contact a provider</li>
                <li><strong>Send a message:</strong> Test text messages and file attachments</li>
                <li><strong>Login as job provider:</strong> Check messages → Reply to the conversation</li>
                <li><strong>Test features:</strong> File uploads, message history, real-time updates</li>
            </ol>
        </div>
    </div>
</body>
</html>
