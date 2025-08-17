<?php
// Test the simplified job seeker registration
require_once __DIR__ . '/inc/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = 'Test Job Seeker ' . rand(1, 999);
        $email = 'testseeker' . rand(1, 999) . '@example.com';
        $phone = '+1234567' . rand(100, 999);
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        // Insert user with simplified fields
        $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, experience_level, bio) VALUES (?, ?, ?, ?, ?, 1, ?, ?)");
        $stmt->execute([
            'job_seeker', 
            $name, 
            $email, 
            $phone, 
            $password,
            'intermediate',
            'Test job seeker with simplified registration'
        ]);
        $user_id = $pdo->lastInsertId();
        
        // Add a category
        $pdo->prepare("INSERT IGNORE INTO user_job_categories (user_id, category_id) VALUES (?, 1)")->execute([$user_id]);
        
        // Add some skills from text
        $skills_text = 'Web Design, Customer Service, Data Entry';
        $skills_array = array_map('trim', explode(',', $skills_text));
        foreach ($skills_array as $skill_name) {
            if (!empty($skill_name)) {
                // Check if skill exists, if not create it
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
        
        $message = "✅ Test job seeker created successfully: $name";
        
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Check current job seekers
try {
    $seekers = $pdo->query("
        SELECT u.*, GROUP_CONCAT(DISTINCT s.name) as skills 
        FROM users u 
        LEFT JOIN user_skills us ON u.id = us.user_id 
        LEFT JOIN skills s ON us.skill_id = s.id 
        WHERE u.role = 'job_seeker' 
        GROUP BY u.id 
        ORDER BY u.created_at DESC 
        LIMIT 5
    ")->fetchAll();
} catch (Exception $e) {
    $seekers = [];
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Simplified Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .message { padding: 15px; margin: 15px 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #005a87; }
        .user-card { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #007cba; }
        .user-name { font-weight: bold; color: #495057; }
        .user-details { font-size: 0.9rem; color: #6c757d; margin-top: 5px; }
        .skills { background: #e3f2fd; padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; margin-top: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Simplified Job Seeker Registration</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>
        
        <div style="margin: 20px 0;">
            <form method="post">
                <button type="submit" class="btn">Create Test Job Seeker</button>
            </form>
            
            <a href="signup.php?role=job_seeker" class="btn" style="background: #28a745;">Try Real Registration</a>
            <a href="debug_users.php" class="btn" style="background: #6c757d;">View All Users</a>
        </div>
        
        <h2>📋 Recent Job Seekers (Last 5)</h2>
        <?php if (empty($seekers)): ?>
            <p style="color: #6c757d;">No job seekers found. Create some test users!</p>
        <?php else: ?>
            <?php foreach ($seekers as $seeker): ?>
                <div class="user-card">
                    <div class="user-name"><?= htmlspecialchars($seeker['name']) ?></div>
                    <div class="user-details">
                        <strong>Email:</strong> <?= htmlspecialchars($seeker['email']) ?><br>
                        <strong>Experience:</strong> <?= htmlspecialchars($seeker['experience_level'] ?: 'Not set') ?><br>
                        <strong>Bio:</strong> <?= htmlspecialchars($seeker['bio'] ?: 'No bio provided') ?>
                    </div>
                    <?php if ($seeker['skills']): ?>
                        <div class="skills">
                            <strong>Skills:</strong> <?= htmlspecialchars($seeker['skills']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 8px;">
            <h3>✅ Simplified Registration Features:</h3>
            <ul>
                <li><strong>Reduced fields:</strong> Only essential information required</li>
                <li><strong>Simple experience levels:</strong> Beginner, Intermediate, Experienced</li>
                <li><strong>Text-based skills:</strong> No complex category selection</li>
                <li><strong>Optional bio:</strong> Users can describe themselves freely</li>
                <li><strong>Single category:</strong> Dropdown selection instead of checkboxes</li>
                <li><strong>Auto-verification:</strong> Users can start using the platform immediately</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
            <h4>🔧 Test Instructions:</h4>
            <ol>
                <li>Click "Create Test Job Seeker" to create a sample user</li>
                <li>Click "Try Real Registration" to test the actual form</li>
                <li>Fill in the simplified form with minimal required fields</li>
                <li>Check that the user appears in the list above</li>
                <li>Verify the user can login and access their dashboard</li>
            </ol>
        </div>
    </div>
</body>
</html>
