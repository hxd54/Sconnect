<?php
/**
 * Quick Server Status Checker
 * This script checks if SmartPath AI server is running
 */

header('Content-Type: application/json');

function checkServerStatus($url) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    return $response !== false;
}

$endpoints = [
    'http://localhost:8000' => 'Main Server',
    'http://localhost:8000/docs' => 'API Documentation',
    'http://localhost:8000/chat' => 'Chat Endpoint',
    'http://localhost:8000/analyze' => 'CV Analysis',
    'http://localhost:8000/match' => 'Job Matching'
];

$status = [];
$server_running = false;

foreach ($endpoints as $url => $name) {
    $is_up = checkServerStatus($url);
    $status[$name] = [
        'url' => $url,
        'status' => $is_up ? 'online' : 'offline',
        'accessible' => $is_up
    ];
    
    if ($is_up) {
        $server_running = true;
    }
}

echo json_encode([
    'server_running' => $server_running,
    'timestamp' => date('c'),
    'endpoints' => $status,
    'message' => $server_running ? 'SmartPath AI server is running!' : 'SmartPath AI server is not running. Please start it first.'
]);
?>
