<?php
// Quick test registration script
require_once __DIR__ . '/inc/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $role = $_POST['role'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, verified, profession, location) VALUES (?, ?, ?, ?, ?, 1, ?, ?)");
        $stmt->execute([$role, $name, $email, $phone, $password, 'Test Company', 'Test Location']);
        $user_id = $pdo->lastInsertId();
        
        if ($role === 'job_seeker') {
            // Add some categories
            $pdo->prepare("INSERT IGNORE INTO user_job_categories (user_id, category_id) VALUES (?, 1)")->execute([$user_id]);
            $pdo->prepare("INSERT IGNORE INTO user_job_categories (user_id, category_id) VALUES (?, 2)")->execute([$user_id]);
            
            // Add some skills
            $pdo->prepare("INSERT IGNORE INTO user_skills (user_id, skill_id) VALUES (?, 1)")->execute([$user_id]);
            $pdo->prepare("INSERT IGNORE INTO user_skills (user_id, skill_id) VALUES (?, 2)")->execute([$user_id]);
            $pdo->prepare("INSERT IGNORE INTO user_skills (user_id, skill_id) VALUES (?, 3)")->execute([$user_id]);
        }
        
        if ($role === 'job_provider') {
            // Create a test job posting
            $pdo->prepare("INSERT INTO job_postings (user_id, title, description, budget_min, budget_max, job_type, status) VALUES (?, ?, ?, ?, ?, ?, 'open')")
                ->execute([$user_id, 'Test Job - ' . $name, 'This is a test job posting created automatically.', 500, 2000, 'contract']);
        }
        
        $message = "Test user created successfully: $name ($role)";
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Registration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; }
        input, select { padding: 8px; width: 200px; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Quick Test Registration</h1>
    
    <?php if ($message): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label>Role:</label>
            <select name="role" required>
                <option value="job_seeker">Job Seeker</option>
                <option value="job_provider">Job Provider</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" value="Test User <?= rand(1, 999) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="test<?= rand(1, 999) ?>@example.com" required>
        </div>
        
        <div class="form-group">
            <label>Phone:</label>
            <input type="text" name="phone" value="+1234567<?= rand(100, 999) ?>" required>
        </div>
        
        <button type="submit">Create Test User</button>
    </form>
    
    <p><a href="debug_users.php">View All Users</a> | <a href="index.php">Homepage</a></p>
</body>
</html>
