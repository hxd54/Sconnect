<?php
// Quick test of database and registration
try {
    require_once 'inc/db.php';
    echo "✅ Database connection successful\n";
    
    // Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Found " . count($tables) . " tables\n";
    
    // Check job categories
    $categories = $pdo->query("SELECT COUNT(*) FROM job_categories")->fetchColumn();
    echo "📂 Job categories: $categories\n";
    
    // Check existing users
    $users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'job_seeker'")->fetchColumn();
    echo "👥 Job seekers: $users\n";
    
    // Test simple user creation
    $test_name = "Test User " . date('His');
    $test_email = "test" . date('His') . "@example.com";
    $test_phone = "+1555" . date('His');
    
    $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, experience_level) VALUES (?, ?, ?, ?, ?, 1, ?)");
    $result = $stmt->execute(['job_seeker', $test_name, $test_email, $test_phone, password_hash('test123', PASSWORD_DEFAULT), 'intermediate']);
    
    if ($result) {
        $user_id = $pdo->lastInsertId();
        echo "✅ Test user created successfully with ID: $user_id\n";
        echo "📧 Email: $test_email\n";
        echo "🔑 Password: test123\n";
        
        // Add category
        $pdo->prepare("INSERT INTO user_job_categories (user_id, category_id) VALUES (?, 1)")->execute([$user_id]);
        echo "📂 Category added\n";
        
        echo "🎉 REGISTRATION TEST: SUCCESS!\n";
    } else {
        echo "❌ Failed to create test user\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
