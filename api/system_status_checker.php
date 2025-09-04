<?php
/**
 * Complete System Status Checker
 * Checks Python installation, SmartPath AI server, and AI integration
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SConnect AI System Status</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        .status-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #6c757d;
        }
        .status-success { border-left-color: #28a745; background: #d4edda; }
        .status-warning { border-left-color: #ffc107; background: #fff3cd; }
        .status-error { border-left-color: #dc3545; background: #f8d7da; }
        .status-info { border-left-color: #17a2b8; background: #d1ecf1; }
        
        .check-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }
        .check-icon {
            width: 30px;
            text-align: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            font-weight: 600;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .btn-warning { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
        
        .code {
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9rem;
        }
        
        .refresh-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <button class="btn refresh-btn" onclick="location.reload()">
        <i class="fas fa-sync-alt"></i> Refresh Status
    </button>

    <div class="container">
        <h1><i class="fas fa-robot"></i> SConnect AI System Status</h1>
        <p>This page checks if your AI system is properly configured and running.</p>

        <?php
        // Check 1: Python Installation
        echo '<div class="status-card">';
        echo '<h3><i class="fas fa-python"></i> Python Installation</h3>';
        
        $python_check = shell_exec('python --version 2>&1');
        if ($python_check && strpos($python_check, 'Python') !== false) {
            echo '<div class="check-item">';
            echo '<div class="check-icon success"><i class="fas fa-check-circle"></i></div>';
            echo '<div><strong>✅ Python is installed!</strong><br>';
            echo 'Version: <span class="code">' . trim($python_check) . '</span></div>';
            echo '</div>';
            
            $pip_check = shell_exec('pip --version 2>&1');
            if ($pip_check && strpos($pip_check, 'pip') !== false) {
                echo '<div class="check-item">';
                echo '<div class="check-icon success"><i class="fas fa-check-circle"></i></div>';
                echo '<div><strong>✅ Pip is available!</strong><br>';
                echo 'Version: <span class="code">' . trim(explode(' ', $pip_check)[1]) . '</span></div>';
                echo '</div>';
            }
        } else {
            echo '<div class="check-item">';
            echo '<div class="check-icon error"><i class="fas fa-times-circle"></i></div>';
            echo '<div><strong>❌ Python is not installed or not in PATH</strong><br>';
            echo 'Please install Python from <a href="https://python.org/downloads/" target="_blank">python.org</a></div>';
            echo '</div>';
        }
        echo '</div>';

        // Check 2: SmartPath AI Directory
        echo '<div class="status-card">';
        echo '<h3><i class="fas fa-folder"></i> SmartPath AI Directory</h3>';
        
        if (is_dir('smartpath-ai')) {
            echo '<div class="check-item">';
            echo '<div class="check-icon success"><i class="fas fa-check-circle"></i></div>';
            echo '<div><strong>✅ SmartPath AI directory found!</strong><br>';
            echo 'Location: <span class="code">smartpath-ai/</span></div>';
            echo '</div>';
            
            if (file_exists('smartpath-ai/backend/main.py')) {
                echo '<div class="check-item">';
                echo '<div class="check-icon success"><i class="fas fa-check-circle"></i></div>';
                echo '<div><strong>✅ Backend main.py found!</strong></div>';
                echo '</div>';
            } else {
                echo '<div class="check-item">';
                echo '<div class="check-icon error"><i class="fas fa-times-circle"></i></div>';
                echo '<div><strong>❌ Backend main.py not found!</strong></div>';
                echo '</div>';
            }
        } else {
            echo '<div class="check-item">';
            echo '<div class="check-icon error"><i class="fas fa-times-circle"></i></div>';
            echo '<div><strong>❌ SmartPath AI directory not found!</strong></div>';
            echo '</div>';
        }
        echo '</div>';

        // Check 3: Server Status
        echo '<div class="status-card">';
        echo '<h3><i class="fas fa-server"></i> SmartPath AI Server Status</h3>';
        
        $server_endpoints = [
            'http://localhost:8000' => 'Main Server',
            'http://localhost:8000/chat' => 'Chat API',
            'http://localhost:8000/analyze' => 'CV Analysis API',
            'http://localhost:8000/match' => 'Job Matching API'
        ];
        
        $server_running = false;
        foreach ($server_endpoints as $url => $name) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 3,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            $is_up = $response !== false;
            
            if ($is_up) $server_running = true;
            
            echo '<div class="check-item">';
            echo '<div class="check-icon ' . ($is_up ? 'success' : 'error') . '">';
            echo '<i class="fas fa-' . ($is_up ? 'check-circle' : 'times-circle') . '"></i></div>';
            echo '<div><strong>' . ($is_up ? '✅' : '❌') . ' ' . $name . '</strong><br>';
            echo '<span class="code">' . $url . '</span></div>';
            echo '</div>';
        }
        
        if (!$server_running) {
            echo '<div class="status-card status-warning">';
            echo '<h4><i class="fas fa-exclamation-triangle"></i> Server Not Running</h4>';
            echo '<p>Your SmartPath AI server is not running. Start it using one of these methods:</p>';
            echo '<a href="#" class="btn btn-success" onclick="startServer()"><i class="fas fa-play"></i> Start Server</a>';
            echo '<a href="start_smartpath_ai.bat" class="btn btn-warning"><i class="fas fa-download"></i> Download Starter</a>';
            echo '</div>';
        }
        echo '</div>';

        // Check 4: AI Integration Files
        echo '<div class="status-card">';
        echo '<h3><i class="fas fa-puzzle-piece"></i> AI Integration Files</h3>';
        
        $ai_files = [
            'ai_chatbot_widget.php' => 'AI Chatbot Widget',
            'cv_scanner_widget.php' => 'CV Scanner Widget', 
            'ai_chat_handler.php' => 'Chat Handler',
            'cv_scan_handler.php' => 'CV Scan Handler'
        ];
        
        foreach ($ai_files as $file => $name) {
            $exists = file_exists($file);
            echo '<div class="check-item">';
            echo '<div class="check-icon ' . ($exists ? 'success' : 'error') . '">';
            echo '<i class="fas fa-' . ($exists ? 'check-circle' : 'times-circle') . '"></i></div>';
            echo '<div><strong>' . ($exists ? '✅' : '❌') . ' ' . $name . '</strong><br>';
            echo '<span class="code">' . $file . '</span></div>';
            echo '</div>';
        }
        echo '</div>';

        // Overall Status
        if ($python_check && $server_running && is_dir('smartpath-ai')) {
            echo '<div class="status-card status-success">';
            echo '<h3><i class="fas fa-check-circle"></i> System Status: READY!</h3>';
            echo '<p>🎉 Your AI system is fully configured and running!</p>';
            echo '<a href="dashboard_job_seeker.php" class="btn btn-success">Test Job Seeker Dashboard</a>';
            echo '<a href="dashboard_job_provider.php" class="btn btn-success">Test Job Provider Dashboard</a>';
            echo '</div>';
        } else {
            echo '<div class="status-card status-warning">';
            echo '<h3><i class="fas fa-exclamation-triangle"></i> System Status: NEEDS SETUP</h3>';
            echo '<p>Some components need to be configured. Follow the steps above to complete setup.</p>';
            echo '</div>';
        }
        ?>

        <div class="status-card status-info">
            <h3><i class="fas fa-terminal"></i> Quick Commands</h3>
            <p>Use these commands in Command Prompt:</p>
            <div style="background: #f1f3f4; padding: 15px; border-radius: 8px; font-family: monospace;">
                <div># Check Python installation</div>
                <div><strong>python --version</strong></div>
                <br>
                <div># Start SmartPath AI Server</div>
                <div><strong>cd C:\xampp\htdocs\Sconnect</strong></div>
                <div><strong>python start_smartpath_server.py</strong></div>
                <br>
                <div># Alternative: Use batch file</div>
                <div><strong>start_smartpath_ai.bat</strong></div>
            </div>
        </div>
    </div>

    <script>
        function startServer() {
            alert('To start the server:\n\n1. Open Command Prompt\n2. Navigate to: C:\\xampp\\htdocs\\Sconnect\n3. Run: python start_smartpath_server.py\n\nOr double-click start_smartpath_ai.bat');
        }
        
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
