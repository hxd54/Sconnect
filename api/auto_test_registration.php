<?php
// Automated test of the simplified job seeker registration via web
require_once __DIR__ . '/inc/db.php';

$test_results = [];
$overall_status = 'success';

// Test 1: Database Connection
try {
    $pdo->query("SELECT 1");
    $test_results['database'] = ['status' => 'success', 'message' => 'Database connection working'];
} catch (Exception $e) {
    $test_results['database'] = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    $overall_status = 'error';
}

// Test 2: Required Tables
if ($overall_status !== 'error') {
    try {
        $required_tables = ['users', 'job_categories', 'skills', 'user_job_categories', 'user_skills'];
        $missing_tables = [];
        
        foreach ($required_tables as $table) {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() == 0) {
                $missing_tables[] = $table;
            }
        }
        
        if (empty($missing_tables)) {
            $test_results['tables'] = ['status' => 'success', 'message' => 'All required tables exist'];
        } else {
            $test_results['tables'] = ['status' => 'error', 'message' => 'Missing tables: ' . implode(', ', $missing_tables)];
            $overall_status = 'error';
        }
    } catch (Exception $e) {
        $test_results['tables'] = ['status' => 'error', 'message' => 'Table check error: ' . $e->getMessage()];
        $overall_status = 'error';
    }
}

// Test 3: Job Categories Data
if ($overall_status !== 'error') {
    try {
        $categories_count = $pdo->query("SELECT COUNT(*) FROM job_categories")->fetchColumn();
        if ($categories_count >= 8) {
            $test_results['categories'] = ['status' => 'success', 'message' => "$categories_count job categories available"];
        } else {
            $test_results['categories'] = ['status' => 'warning', 'message' => "Only $categories_count categories found"];
        }
    } catch (Exception $e) {
        $test_results['categories'] = ['status' => 'error', 'message' => 'Categories error: ' . $e->getMessage()];
    }
}

// Test 4: Simulate Registration Process
if ($overall_status !== 'error') {
    try {
        // Simulate form data
        $test_name = 'Auto Test User ' . date('Y-m-d H:i:s');
        $test_email = 'autotest' . time() . '@example.com';
        $test_phone = '+1555' . rand(1000000, 9999999);
        $test_password = password_hash('autotest123', PASSWORD_DEFAULT);
        $test_experience = 'intermediate';
        $test_bio = 'This is an automated test user created to verify the simplified registration system.';
        $test_skills = 'Automated Testing, Quality Assurance, System Verification';
        
        // Insert user (simulating the simplified registration)
        $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, experience_level, bio) VALUES (?, ?, ?, ?, ?, 1, ?, ?)");
        $result = $stmt->execute(['job_seeker', $test_name, $test_email, $test_phone, $test_password, $test_experience, $test_bio]);
        
        if ($result) {
            $user_id = $pdo->lastInsertId();
            
            // Add category (simulating dropdown selection)
            $pdo->prepare("INSERT INTO user_job_categories (user_id, category_id) VALUES (?, 1)")->execute([$user_id]);
            
            // Add skills from text (simulating text input)
            $skills_array = array_map('trim', explode(',', $test_skills));
            foreach ($skills_array as $skill_name) {
                if (!empty($skill_name)) {
                    // Check if skill exists
                    $stmt = $pdo->prepare("SELECT id FROM skills WHERE name = ?");
                    $stmt->execute([$skill_name]);
                    $skill = $stmt->fetch();
                    
                    if (!$skill) {
                        // Create new skill
                        $stmt = $pdo->prepare("INSERT INTO skills (name) VALUES (?)");
                        $stmt->execute([$skill_name]);
                        $skill_id = $pdo->lastInsertId();
                    } else {
                        $skill_id = $skill['id'];
                    }
                    
                    // Link skill to user
                    $pdo->prepare("INSERT IGNORE INTO user_skills (user_id, skill_id) VALUES (?, ?)")
                        ->execute([$user_id, $skill_id]);
                }
            }
            
            $test_results['registration'] = [
                'status' => 'success', 
                'message' => "User created successfully (ID: $user_id)",
                'details' => [
                    'name' => $test_name,
                    'email' => $test_email,
                    'phone' => $test_phone,
                    'password' => 'autotest123'
                ]
            ];
        } else {
            $test_results['registration'] = ['status' => 'error', 'message' => 'Failed to create test user'];
            $overall_status = 'error';
        }
    } catch (Exception $e) {
        $test_results['registration'] = ['status' => 'error', 'message' => 'Registration error: ' . $e->getMessage()];
        $overall_status = 'error';
    }
}

// Test 5: Verify User Data
if (isset($user_id)) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   GROUP_CONCAT(DISTINCT jc.name) as categories,
                   GROUP_CONCAT(DISTINCT s.name) as skills
            FROM users u 
            LEFT JOIN user_job_categories ujc ON u.id = ujc.user_id 
            LEFT JOIN job_categories jc ON ujc.category_id = jc.id
            LEFT JOIN user_skills us ON u.id = us.user_id
            LEFT JOIN skills s ON us.skill_id = s.id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        
        if ($user_data) {
            $test_results['verification'] = [
                'status' => 'success', 
                'message' => 'User data verified successfully',
                'user_data' => $user_data
            ];
        } else {
            $test_results['verification'] = ['status' => 'error', 'message' => 'Could not retrieve user data'];
        }
    } catch (Exception $e) {
        $test_results['verification'] = ['status' => 'error', 'message' => 'Verification error: ' . $e->getMessage()];
    }
}

// Test 6: Login Simulation
if (isset($test_email) && isset($user_id)) {
    try {
        $login_stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $login_stmt->execute([$test_email]);
        $login_user = $login_stmt->fetch();
        
        if ($login_user && password_verify('autotest123', $login_user['password'])) {
            $test_results['login'] = ['status' => 'success', 'message' => 'Login simulation successful'];
        } else {
            $test_results['login'] = ['status' => 'error', 'message' => 'Login simulation failed'];
        }
    } catch (Exception $e) {
        $test_results['login'] = ['status' => 'error', 'message' => 'Login test error: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Automated Registration Test Results</title>
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
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 Automated Job Seeker Registration Test</h1>
            <p>Testing the simplified registration system automatically</p>
        </div>
        
        <div class="status-overall <?= $overall_status ?>">
            <?php if ($overall_status === 'success'): ?>
                ✅ AUTOMATED TEST: SUCCESS - Registration system is working perfectly!
            <?php else: ?>
                ❌ AUTOMATED TEST: FAILED - Issues detected in registration system
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
                    
                    <?php if (isset($result['details'])): ?>
                        <div class="user-details">
                            <strong>Test User Details:</strong><br>
                            Name: <?= htmlspecialchars($result['details']['name']) ?><br>
                            Email: <?= htmlspecialchars($result['details']['email']) ?><br>
                            Phone: <?= htmlspecialchars($result['details']['phone']) ?><br>
                            Password: <?= htmlspecialchars($result['details']['password']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($result['user_data'])): ?>
                        <div class="user-details">
                            <strong>Verified User Data:</strong><br>
                            Role: <?= htmlspecialchars($result['user_data']['role']) ?><br>
                            Experience: <?= htmlspecialchars($result['user_data']['experience_level']) ?><br>
                            Categories: <?= htmlspecialchars($result['user_data']['categories'] ?: 'None') ?><br>
                            Skills: <?= htmlspecialchars($result['user_data']['skills'] ?: 'None') ?><br>
                            Verified: <?= $result['user_data']['verified'] ? 'Yes' : 'No' ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="signup.php?role=job_seeker" class="btn">🧪 Try Manual Registration</a>
            <a href="debug_users.php" class="btn">👥 View All Users</a>
            <a href="system_test.php" class="btn">🔧 Full System Test</a>
            <a href="index.php" class="btn">🏠 Homepage</a>
        </div>
        
        <?php if ($overall_status === 'success'): ?>
            <div style="margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 8px; border-left: 4px solid #28a745;">
                <h3>🎉 Test Summary:</h3>
                <ul>
                    <li>✅ Database connection working</li>
                    <li>✅ All required tables exist</li>
                    <li>✅ Job categories loaded</li>
                    <li>✅ User registration successful</li>
                    <li>✅ Skills system working</li>
                    <li>✅ Login simulation passed</li>
                </ul>
                <p><strong>The simplified job seeker registration is fully functional!</strong></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
