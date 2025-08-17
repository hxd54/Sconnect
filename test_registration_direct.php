<?php
// Direct test of the simplified job seeker registration
require_once __DIR__ . '/inc/db.php';

echo "Testing simplified job seeker registration...\n";

try {
    // Test data
    $name = 'Test User ' . rand(1000, 9999);
    $email = 'testuser' . rand(1000, 9999) . '@example.com';
    $phone = '+1555' . rand(1000000, 9999999);
    $password = password_hash('testpass123', PASSWORD_DEFAULT);
    $experience_level = 'intermediate';
    $bio = 'I am a test user created to verify the simplified registration system works correctly.';
    $skills_text = 'Web Design, Customer Service, Data Analysis';
    $selected_categories = [1]; // First category
    
    echo "Creating user: $name\n";
    echo "Email: $email\n";
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, experience_level, bio) VALUES (?, ?, ?, ?, ?, 1, ?, ?)");
    $stmt->execute(['job_seeker', $name, $email, $phone, $password, $experience_level, $bio]);
    $user_id = $pdo->lastInsertId();
    
    echo "User created with ID: $user_id\n";
    
    // Save categories
    foreach ($selected_categories as $category_id) {
        $pdo->prepare("INSERT IGNORE INTO user_job_categories (user_id, category_id) VALUES (?, ?)")
            ->execute([$user_id, (int)$category_id]);
    }
    echo "Categories saved\n";
    
    // Save skills from text input (simplified approach)
    if (!empty($skills_text)) {
        $skills_array = array_map('trim', explode(',', $skills_text));
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
                    echo "Created new skill: $skill_name (ID: $skill_id)\n";
                } else {
                    $skill_id = $skill['id'];
                    echo "Using existing skill: $skill_name (ID: $skill_id)\n";
                }
                
                // Link skill to user
                $pdo->prepare("INSERT IGNORE INTO user_skills (user_id, skill_id) VALUES (?, ?)")
                    ->execute([$user_id, $skill_id]);
            }
        }
    }
    
    echo "Skills saved successfully\n";
    
    // Verify the user was created correctly
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
    $user = $stmt->fetch();
    
    echo "\n=== USER VERIFICATION ===\n";
    echo "Name: " . $user['name'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    echo "Experience: " . $user['experience_level'] . "\n";
    echo "Verified: " . ($user['verified'] ? 'Yes' : 'No') . "\n";
    echo "Categories: " . ($user['categories'] ?: 'None') . "\n";
    echo "Skills: " . ($user['skills'] ?: 'None') . "\n";
    echo "Bio: " . $user['bio'] . "\n";
    
    echo "\n✅ SUCCESS: Job seeker registration test completed successfully!\n";
    
    // Test login simulation
    echo "\n=== TESTING LOGIN SIMULATION ===\n";
    $login_stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $login_stmt->execute([$email]);
    $login_user = $login_stmt->fetch();
    
    if ($login_user && password_verify('testpass123', $login_user['password'])) {
        echo "✅ Login test: SUCCESS - Password verification works\n";
        echo "User can login with email: $email and password: testpass123\n";
    } else {
        echo "❌ Login test: FAILED - Password verification failed\n";
    }
    
    // Count total job seekers
    $count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'job_seeker'")->fetchColumn();
    echo "\nTotal job seekers in database: $count\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
