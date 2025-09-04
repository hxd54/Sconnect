<?php
/**
 * Test connection to SmartPath AI backend
 * This script tests if the SmartPath AI server is running and responding
 */

echo "<h2>Testing SmartPath AI Connection</h2>";

// Test endpoints
$endpoints = [
    'http://localhost:8000/chat' => 'Chat Endpoint',
    'http://localhost:8000/analyze' => 'CV Analysis Endpoint',
    'http://localhost:8000/match' => 'Job Matching Endpoint'
];

foreach ($endpoints as $endpoint => $name) {
    echo "<h3>Testing: $name ($endpoint)</h3>";
    
    try {
        $postData = http_build_query([
            'message' => 'Hello, this is a test message',
            'speak_response' => false,
            'language' => 'en'
        ]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData,
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($endpoint, false, $context);
        
        if ($response !== false) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>✅ SUCCESS!</strong><br>";
            echo "<strong>Response:</strong><br>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
            echo "</div>";
            
            $data = json_decode($response, true);
            if ($data) {
                echo "<strong>Parsed JSON:</strong><br>";
                echo "<pre>" . print_r($data, true) . "</pre>";
            }
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>❌ FAILED</strong><br>";
            echo "No response received from $endpoint<br>";
            echo "Make sure SmartPath AI server is running on port 8000";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ ERROR</strong><br>";
        echo "Error: " . $e->getMessage();
        echo "</div>";
    }
    
    echo "<hr>";
}

echo "<h3>🚀 How to Start SmartPath AI Server:</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<h4>Method 1: Easy Start (Recommended)</h4>";
echo "<ol>";
echo "<li>Double-click: <code><strong>start_smartpath_ai.bat</strong></code> (in SConnect folder)</li>";
echo "<li>Wait for the server to start on port 8000</li>";
echo "<li>Keep the command window open</li>";
echo "<li>Refresh this page to test connection</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #f0f8f0; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<h4>Method 2: Manual Start</h4>";
echo "<ol>";
echo "<li>Open Command Prompt</li>";
echo "<li>Navigate to: <code>C:\\xampp\\htdocs\\Sconnect\\smartpath-ai</code></li>";
echo "<li>Run: <code>python -m uvicorn backend.main:app --port 8000</code></li>";
echo "<li>Wait for server to start</li>";
echo "<li>Refresh this page</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff8e1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<h4>Method 3: Python Script</h4>";
echo "<ol>";
echo "<li>Run: <code>python start_smartpath_server.py</code></li>";
echo "<li>Follow the on-screen instructions</li>";
echo "</ol>";
echo "</div>";

echo "<h3>📋 Server Status Check:</h3>";
echo "<p>Once the server is running, you should see:</p>";
echo "<ul>";
echo "<li>✅ Server running on: http://localhost:8000</li>";
echo "<li>✅ API docs available at: http://localhost:8000/docs</li>";
echo "<li>✅ All endpoints responding</li>";
echo "</ul>";

echo "<h3>🔧 Troubleshooting:</h3>";
echo "<ul>";
echo "<li>Make sure Python is installed</li>";
echo "<li>Check if port 8000 is available</li>";
echo "<li>Verify all dependencies are installed</li>";
echo "<li>Check the command window for error messages</li>";
echo "</ul>";
?>
