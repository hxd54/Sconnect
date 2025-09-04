<?php
/**
 * Python Installation and SmartPath AI Connection Checker
 * This script checks if Python is installed and helps connect to SmartPath AI
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Python & SmartPath AI Connection Checker</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        .status-box {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid;
        }
        .success { background: #d4edda; border-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        .info { background: #d1ecf1; border-color: #17a2b8; color: #0c5460; }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin: 10px 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
        .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        .step { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #667eea; }
        h1, h2, h3 { color: #333; }
        .loading { text-align: center; padding: 20px; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐍 Python & SmartPath AI Connection Checker</h1>
        
        <div id="python-check">
            <h2>Step 1: Checking Python Installation</h2>
            <div class="loading">
                <div class="spinner"></div>
                <p>Checking Python installation...</p>
            </div>
        </div>

        <div id="server-check" style="display: none;">
            <h2>Step 2: SmartPath AI Server Status</h2>
            <div class="loading">
                <div class="spinner"></div>
                <p>Checking SmartPath AI server...</p>
            </div>
        </div>

        <div id="connection-test" style="display: none;">
            <h2>Step 3: Testing AI Connections</h2>
            <div class="loading">
                <div class="spinner"></div>
                <p>Testing AI endpoints...</p>
            </div>
        </div>

        <div id="results" style="display: none;">
            <h2>🎉 Setup Complete!</h2>
        </div>

        <div id="instructions" style="display: none;">
            <h2>📋 Next Steps</h2>
        </div>
    </div>

    <script>
        // Check Python installation
        async function checkPython() {
            try {
                const response = await fetch('check_python_status.php');
                const data = await response.json();
                
                const pythonCheck = document.getElementById('python-check');
                
                if (data.python_installed) {
                    pythonCheck.innerHTML = `
                        <h2>Step 1: Python Installation ✅</h2>
                        <div class="status-box success">
                            <strong>✅ Python is installed!</strong><br>
                            Version: ${data.python_version}<br>
                            Pip version: ${data.pip_version}
                        </div>
                    `;
                    
                    // Move to server check
                    setTimeout(checkServer, 1000);
                } else {
                    pythonCheck.innerHTML = `
                        <h2>Step 1: Python Installation ❌</h2>
                        <div class="status-box error">
                            <strong>❌ Python is not installed or not in PATH</strong><br>
                            Please install Python first.
                        </div>
                        <div class="step">
                            <h3>🔧 How to Install Python:</h3>
                            <ol>
                                <li>Go to <a href="https://python.org/downloads" target="_blank">python.org/downloads</a></li>
                                <li>Download Python 3.11 or 3.12</li>
                                <li><strong>IMPORTANT:</strong> Check "Add Python to PATH" during installation</li>
                                <li>Restart your computer after installation</li>
                                <li>Refresh this page to check again</li>
                            </ol>
                        </div>
                        <button class="btn" onclick="location.reload()">🔄 Check Again</button>
                    `;
                }
            } catch (error) {
                document.getElementById('python-check').innerHTML = `
                    <div class="status-box error">
                        <strong>❌ Error checking Python:</strong> ${error.message}
                    </div>
                `;
            }
        }

        // Check SmartPath AI server
        async function checkServer() {
            document.getElementById('server-check').style.display = 'block';
            
            try {
                const response = await fetch('check_server_status.php');
                const data = await response.json();
                
                const serverCheck = document.getElementById('server-check');
                
                if (data.server_running) {
                    serverCheck.innerHTML = `
                        <h2>Step 2: SmartPath AI Server ✅</h2>
                        <div class="status-box success">
                            <strong>✅ SmartPath AI server is running!</strong><br>
                            Server URL: <a href="http://localhost:8000" target="_blank">http://localhost:8000</a>
                        </div>
                    `;
                    
                    // Move to connection test
                    setTimeout(testConnections, 1000);
                } else {
                    serverCheck.innerHTML = `
                        <h2>Step 2: SmartPath AI Server ❌</h2>
                        <div class="status-box warning">
                            <strong>⚠️ SmartPath AI server is not running</strong><br>
                            Please start the server to enable AI features.
                        </div>
                        <div class="step">
                            <h3>🚀 How to Start SmartPath AI Server:</h3>
                            <div class="code">
                                <strong>Option 1 (Easy):</strong><br>
                                Double-click: <strong>start_smartpath_ai.bat</strong>
                            </div>
                            <div class="code">
                                <strong>Option 2 (Command Line):</strong><br>
                                cd C:\\xampp\\htdocs\\Sconnect\\smartpath-ai<br>
                                python -m uvicorn backend.main:app --port 8000
                            </div>
                            <div class="code">
                                <strong>Option 3 (Python Script):</strong><br>
                                python start_smartpath_server.py
                            </div>
                        </div>
                        <button class="btn" onclick="checkServer()">🔄 Check Server Again</button>
                        <a href="start_smartpath_ai.bat" class="btn">🚀 Start Server (Batch)</a>
                    `;
                }
            } catch (error) {
                document.getElementById('server-check').innerHTML = `
                    <div class="status-box error">
                        <strong>❌ Error checking server:</strong> ${error.message}
                    </div>
                `;
            }
        }

        // Test AI connections
        async function testConnections() {
            document.getElementById('connection-test').style.display = 'block';
            
            try {
                const response = await fetch('test_ai_connection.php');
                const data = await response.json();
                
                const connectionTest = document.getElementById('connection-test');
                
                if (data.all_working) {
                    connectionTest.innerHTML = `
                        <h2>Step 3: AI Connections ✅</h2>
                        <div class="status-box success">
                            <strong>✅ All AI features are working!</strong><br>
                            Chat: ${data.chat_working ? '✅' : '❌'}<br>
                            CV Analysis: ${data.cv_analysis_working ? '✅' : '❌'}<br>
                            Job Matching: ${data.job_matching_working ? '✅' : '❌'}
                        </div>
                    `;
                    
                    showResults();
                } else {
                    connectionTest.innerHTML = `
                        <h2>Step 3: AI Connections ⚠️</h2>
                        <div class="status-box warning">
                            <strong>⚠️ Some AI features may not work properly</strong><br>
                            Chat: ${data.chat_working ? '✅' : '❌'}<br>
                            CV Analysis: ${data.cv_analysis_working ? '✅' : '❌'}<br>
                            Job Matching: ${data.job_matching_working ? '✅' : '❌'}
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('connection-test').innerHTML = `
                    <div class="status-box error">
                        <strong>❌ Error testing connections:</strong> ${error.message}
                    </div>
                `;
            }
        }

        // Show final results
        function showResults() {
            document.getElementById('results').style.display = 'block';
            document.getElementById('results').innerHTML = `
                <h2>🎉 Setup Complete!</h2>
                <div class="status-box success">
                    <strong>✅ Everything is working perfectly!</strong><br>
                    Your SConnect platform now has full AI capabilities.
                </div>
                <div class="step">
                    <h3>🚀 What's Now Available:</h3>
                    <ul>
                        <li>✅ <strong>AI Chatbot:</strong> Career guidance and job advice</li>
                        <li>✅ <strong>CV Scanner:</strong> AI-powered resume analysis</li>
                        <li>✅ <strong>Job Matching:</strong> Smart job recommendations</li>
                        <li>✅ <strong>Skill Analysis:</strong> Professional development insights</li>
                    </ul>
                </div>
                <a href="dashboard_job_seeker.php" class="btn">🎯 Test Job Seeker Dashboard</a>
                <a href="dashboard_job_provider.php" class="btn">🏢 Test Job Provider Dashboard</a>
                <a href="http://localhost:8000/docs" target="_blank" class="btn">📚 View API Docs</a>
            `;
        }

        // Start the checking process
        document.addEventListener('DOMContentLoaded', function() {
            checkPython();
        });
    </script>
</body>
</html>
