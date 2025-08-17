<?php
// Debug page to check what users are in the database
require_once __DIR__ . '/inc/db.php';

try {
    // Check all users
    echo "<h2>All Users in Database:</h2>";
    $users = $pdo->query("SELECT id, name, email, role, verified, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    
    if (empty($users)) {
        echo "<p>No users found in database.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . ($user['verified'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check job providers specifically
    echo "<h2>Job Providers:</h2>";
    $providers = $pdo->query("SELECT * FROM users WHERE role = 'job_provider' ORDER BY created_at DESC")->fetchAll();
    
    if (empty($providers)) {
        echo "<p>No job providers found.</p>";
    } else {
        foreach ($providers as $provider) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h3>" . htmlspecialchars($provider['name']) . "</h3>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($provider['email']) . "</p>";
            echo "<p><strong>Company:</strong> " . htmlspecialchars($provider['profession'] ?: 'Not set') . "</p>";
            echo "<p><strong>Location:</strong> " . htmlspecialchars($provider['location'] ?: 'Not set') . "</p>";
            echo "<p><strong>Bio:</strong> " . htmlspecialchars($provider['bio'] ?: 'Not set') . "</p>";
            echo "<p><strong>Rating:</strong> " . $provider['rating'] . "/5.0</p>";
            echo "<p><strong>Verified:</strong> " . ($provider['verified'] ? 'Yes' : 'No') . "</p>";
            echo "</div>";
        }
    }
    
    // Check job seekers specifically
    echo "<h2>Job Seekers:</h2>";
    $seekers = $pdo->query("SELECT * FROM users WHERE role = 'job_seeker' ORDER BY created_at DESC")->fetchAll();
    
    if (empty($seekers)) {
        echo "<p>No job seekers found.</p>";
    } else {
        foreach ($seekers as $seeker) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h3>" . htmlspecialchars($seeker['name']) . "</h3>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($seeker['email']) . "</p>";
            echo "<p><strong>Experience:</strong> " . htmlspecialchars($seeker['experience_level'] ?: 'Not set') . "</p>";
            echo "<p><strong>Availability:</strong> " . htmlspecialchars($seeker['availability'] ?: 'Not set') . "</p>";
            echo "<p><strong>Hourly Rate:</strong> $" . ($seeker['hourly_rate'] ?: 'Not set') . "</p>";
            echo "<p><strong>Location:</strong> " . htmlspecialchars($seeker['location'] ?: 'Not set') . "</p>";
            echo "<p><strong>Bio:</strong> " . htmlspecialchars($seeker['bio'] ?: 'Not set') . "</p>";
            echo "<p><strong>Rating:</strong> " . $seeker['rating'] . "/5.0</p>";
            echo "<p><strong>Verified:</strong> " . ($seeker['verified'] ? 'Yes' : 'No') . "</p>";
            
            // Check categories
            $categories = $pdo->prepare("
                SELECT jc.name
                FROM job_categories jc
                JOIN user_job_categories ujc ON jc.id = ujc.category_id
                WHERE ujc.user_id = ?
            ");
            $categories->execute([$seeker['id']]);
            $user_categories = $categories->fetchAll(PDO::FETCH_COLUMN);
            echo "<p><strong>Categories:</strong> " . (empty($user_categories) ? 'None' : implode(', ', $user_categories)) . "</p>";

            // Check skills
            $skills = $pdo->prepare("
                SELECT s.name
                FROM skills s
                JOIN user_skills us ON s.id = us.skill_id
                WHERE us.user_id = ?
            ");
            $skills->execute([$seeker['id']]);
            $user_skills = $skills->fetchAll(PDO::FETCH_COLUMN);
            echo "<p><strong>Skills:</strong> " . (empty($user_skills) ? 'None' : implode(', ', $user_skills)) . "</p>";
            
            echo "</div>";
        }
    }
    
    // Check job postings
    echo "<h2>Job Postings:</h2>";
    $jobs = $pdo->query("
        SELECT jp.*, u.name as company_name 
        FROM job_postings jp 
        JOIN users u ON jp.user_id = u.id 
        ORDER BY jp.created_at DESC
    ")->fetchAll();
    
    if (empty($jobs)) {
        echo "<p>No job postings found.</p>";
    } else {
        foreach ($jobs as $job) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h3>" . htmlspecialchars($job['title']) . "</h3>";
            echo "<p><strong>Company:</strong> " . htmlspecialchars($job['company_name']) . "</p>";
            echo "<p><strong>Type:</strong> " . htmlspecialchars($job['job_type']) . "</p>";
            echo "<p><strong>Status:</strong> " . htmlspecialchars($job['status']) . "</p>";
            echo "<p><strong>Description:</strong> " . htmlspecialchars(substr($job['description'], 0, 200)) . "...</p>";
            if ($job['budget_min'] || $job['budget_max']) {
                echo "<p><strong>Budget:</strong> $" . number_format($job['budget_min']) . " - $" . number_format($job['budget_max']) . "</p>";
            }
            echo "<p><strong>Created:</strong> " . $job['created_at'] . "</p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>

<p><a href="index.php">← Back to Homepage</a></p>
