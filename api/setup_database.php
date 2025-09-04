<?php
// Database Setup Helper
// This page helps you set up the database tables

require_once __DIR__ . '/inc/db.php';

$message = '';
$error = '';
$tables_exist = false;

// Check if tables exist
try {
    $result = $pdo->query("SHOW TABLES LIKE 'job_categories'");
    $tables_exist = $result->rowCount() > 0;
} catch (PDOException $e) {
    $error = "Database connection error: " . $e->getMessage();
}

// Handle database setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_database'])) {
    try {
        // Read and execute the SQL file
        $sql_file = __DIR__ . '/complete_database_setup.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("SQL setup file not found: complete_database_setup.sql");
        }
        
        $sql_content = file_get_contents($sql_file);
        
        // Remove CREATE DATABASE and USE statements for safety
        $sql_content = preg_replace('/CREATE DATABASE.*?;/i', '', $sql_content);
        $sql_content = preg_replace('/USE\s+\w+\s*;/i', '', $sql_content);
        
        // Split into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));
        
        $pdo->beginTransaction();
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                $pdo->exec($statement);
            }
        }
        
        $pdo->commit();
        $message = "Database tables created successfully!";
        $tables_exist = true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error setting up database: " . $e->getMessage();
    }
}

// Check table status
$table_status = [];
if (!$error) {
    $required_tables = [
        'job_categories', 'skills', 'skill_categories', 'users', 'email_tokens', 'user_job_categories',
        'user_skills', 'job_postings', 'portfolio', 'conversations',
        'messages', 'post_likes', 'reviews', 'notifications', 'mentorship_requests'
    ];
    
    foreach ($required_tables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            $table_status[$table] = $result->rowCount() > 0;
        } catch (PDOException $e) {
            $table_status[$table] = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Setup - Sconnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0; 
            min-height: 100vh;
            padding: 2rem;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: #fff; 
            border-radius: 20px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        h1 { margin: 0; font-size: 2.5rem; font-weight: 700; }
        .content { padding: 2rem; }
        .status-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .success { 
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #14532d; 
            border-left: 4px solid #10b981;
        }
        .error { 
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b; 
            border-left: 4px solid #ef4444;
        }
        .warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        .btn {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
        }
        .btn.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .table-status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .table-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f1f5f9;
            border-radius: 8px;
        }
        .table-item.exists { background: #dcfce7; color: #14532d; }
        .table-item.missing { background: #fee2e2; color: #991b1b; }
        .instructions {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .instructions h3 {
            margin-top: 0;
            color: #0369a1;
        }
        .instructions ol {
            margin: 0;
            padding-left: 1.5rem;
        }
        .instructions li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-database"></i> Database Setup</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Sconnect Job Marketplace Platform</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="status-card success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="status-card error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($tables_exist): ?>
                <div class="status-card success">
                    <h3><i class="fas fa-check-circle"></i> Database is Ready!</h3>
                    <p>All required tables have been created successfully. You can now use the Sconnect platform.</p>
                    <a href="signup.php" class="btn success">
                        <i class="fas fa-user-plus"></i> Go to Registration
                    </a>
                    <a href="login.php" class="btn" style="margin-left: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                </div>
            <?php else: ?>
                <div class="status-card warning">
                    <h3><i class="fas fa-exclamation-triangle"></i> Database Setup Required</h3>
                    <p>The database tables need to be created before you can use Sconnect.</p>
                    
                    <form method="post" style="margin-top: 1rem;">
                        <button type="submit" name="setup_database" class="btn" onclick="return confirm('This will create all database tables. Continue?')">
                            <i class="fas fa-cog"></i> Setup Database Now
                        </button>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($table_status)): ?>
                <div class="status-card">
                    <h3>Table Status</h3>
                    <div class="table-status">
                        <?php foreach ($table_status as $table => $exists): ?>
                            <div class="table-item <?= $exists ? 'exists' : 'missing' ?>">
                                <i class="fas fa-<?= $exists ? 'check' : 'times' ?>"></i>
                                <?= htmlspecialchars($table) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="instructions">
                <h3><i class="fas fa-info-circle"></i> Manual Setup Instructions</h3>
                <p>If the automatic setup doesn't work, you can manually set up the database:</p>
                <ol>
                    <li>Open <strong>phpMyAdmin</strong> in your browser (usually <code>http://localhost/phpmyadmin</code>)</li>
                    <li>Select your <strong>sconnect</strong> database (or create it if it doesn't exist)</li>
                    <li>Go to the <strong>SQL</strong> tab</li>
                    <li>Copy and paste the contents of <code>complete_database_setup.sql</code></li>
                    <li>Click <strong>Go</strong> to execute the script</li>
                    <li>Refresh this page to check the status</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>
