<?php
// Quick Database Reset Script
// This script drops all tables and recreates them fresh

require_once __DIR__ . '/inc/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_database'])) {
    try {
        // Get all tables in the database
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        // Disable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Drop all existing tables
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
        }
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Read and execute the complete setup script
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
        $message = "Database reset successfully! All tables recreated with fresh data.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error resetting database: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Database - Sconnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            margin: 0; 
            min-height: 100vh;
            padding: 2rem;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: #fff; 
            border-radius: 20px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        h1 { margin: 0; font-size: 2.5rem; font-weight: 700; }
        .content { padding: 2rem; }
        .warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border-left: 4px solid #f59e0b;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .success { 
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #14532d; 
            border-left: 4px solid #10b981;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .error { 
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b; 
            border-left: 4px solid #ef4444;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
        }
        .btn.secondary {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .btn.secondary:hover {
            background: rgba(239, 68, 68, 0.2);
        }
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-exclamation-triangle"></i> Reset Database</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Danger Zone - Use with Caution</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="warning">
                <h3><i class="fas fa-exclamation-triangle"></i> Warning!</h3>
                <p><strong>This action will permanently delete ALL data in your database!</strong></p>
                <ul>
                    <li>All user accounts will be deleted</li>
                    <li>All job postings will be deleted</li>
                    <li>All messages and conversations will be deleted</li>
                    <li>All portfolio items will be deleted</li>
                    <li>This action cannot be undone!</li>
                </ul>
                <p>Only use this if you want to start completely fresh or if you're having database issues.</p>
            </div>
            
            <form method="post">
                <button type="submit" name="reset_database" class="btn" 
                        onclick="return confirm('Are you ABSOLUTELY SURE you want to delete ALL data? This cannot be undone!')">
                    <i class="fas fa-trash"></i> Reset Database (Delete All Data)
                </button>
            </form>
            
            <div class="actions">
                <a href="setup_database.php" class="btn secondary">
                    <i class="fas fa-arrow-left"></i> Back to Setup
                </a>
                <a href="index.php" class="btn secondary">
                    <i class="fas fa-home"></i> Go to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html>
