<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';

echo "<h1>Login System Test</h1>";

// Check if user is logged in
if (is_logged_in()) {
    $user = current_user();
    echo "<h2>✅ User is logged in!</h2>";
    echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($user['name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
    echo "<p><strong>Role:</strong> " . htmlspecialchars($user['role']) . "</p>";
    echo "<p><strong>Phone:</strong> " . htmlspecialchars($user['phone']) . "</p>";
    
    echo "<h3>Dashboard Links:</h3>";
    if ($user['role'] === 'job_seeker') {
        echo "<p><a href='dashboard_job_seeker.php'>Go to Job Seeker Dashboard</a></p>";
    } elseif ($user['role'] === 'job_provider') {
        echo "<p><a href='dashboard_job_provider.php'>Go to Job Provider Dashboard</a></p>";
    }
    
    echo "<p><a href='logout.php'>Logout</a></p>";
} else {
    echo "<h2>❌ User is not logged in</h2>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    echo "<p><a href='signup.php'>Go to Signup</a></p>";
}

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>✅ Database connected successfully</p>";
    echo "<p>Total users in database: " . $result['count'] . "</p>";
    
    // Check for job seekers and providers
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roles = $stmt->fetchAll();
    echo "<h3>User Roles:</h3>";
    foreach ($roles as $role) {
        echo "<p>" . htmlspecialchars($role['role']) . ": " . $role['count'] . " users</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test post_likes table
echo "<h2>Post Likes Table Test</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM post_likes");
    $result = $stmt->fetch();
    echo "<p>✅ post_likes table exists</p>";
    echo "<p>Total likes in database: " . $result['count'] . "</p>";
} catch (PDOException $e) {
    echo "<p>❌ post_likes table error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test job_postings table
echo "<h2>Job Postings Table Test</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_postings");
    $result = $stmt->fetch();
    echo "<p>✅ job_postings table exists</p>";
    echo "<p>Total job postings: " . $result['count'] . "</p>";
} catch (PDOException $e) {
    echo "<p>❌ job_postings table error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Back to Home</a></p>";
?>
