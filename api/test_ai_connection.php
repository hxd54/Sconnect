<?php
/**
 * AI Connection Tester
 * Tests all AI endpoints to ensure they're working
 */

header('Content-Type: application/json');

function testEndpoint($url, $method = 'GET', $data = null) {
    $context_options = [
        'http' => [
            'method' => $method,
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ];
    
    if ($method === 'POST' && $data) {
        $context_options['http']['header'] = 'Content-Type: application/x-www-form-urlencoded';
        $context_options['http']['content'] = http_build_query($data);
    }
    
    $context = stream_context_create($context_options);
    $response = @file_get_contents($url, false, $context);
    
    return [
        'success' => $response !== false,
        'response' => $response,
        'http_code' => $http_response_header[0] ?? 'Unknown'
    ];
}

// Test main server
$server_test = testEndpoint('http://localhost:8000');

// Test chat endpoint
$chat_test = testEndpoint('http://localhost:8000/chat', 'POST', [
    'message' => 'Hello, this is a test',
    'speak_response' => false,
    'language' => 'en'
]);

// Test analyze endpoint (without file for now)
$analyze_test = testEndpoint('http://localhost:8000/analyze');

// Test match endpoint (without file for now)
$match_test = testEndpoint('http://localhost:8000/match');

// Test API docs
$docs_test = testEndpoint('http://localhost:8000/docs');

$results = [
    'server_running' => $server_test['success'],
    'chat_working' => $chat_test['success'],
    'cv_analysis_working' => $analyze_test['success'] || strpos($analyze_test['http_code'], '422') !== false, // 422 is expected without file
    'job_matching_working' => $match_test['success'] || strpos($match_test['http_code'], '422') !== false, // 422 is expected without file
    'docs_available' => $docs_test['success'],
    'all_working' => false,
    'details' => [
        'server' => $server_test,
        'chat' => $chat_test,
        'analyze' => $analyze_test,
        'match' => $match_test,
        'docs' => $docs_test
    ],
    'timestamp' => date('c')
];

// Check if all critical features are working
$results['all_working'] = $results['server_running'] && 
                         $results['chat_working'] && 
                         $results['cv_analysis_working'] && 
                         $results['job_matching_working'];

// Add helpful messages
if ($results['all_working']) {
    $results['message'] = 'All AI features are working perfectly!';
} elseif ($results['server_running']) {
    $results['message'] = 'Server is running but some endpoints may need attention.';
} else {
    $results['message'] = 'SmartPath AI server is not running. Please start it first.';
}

echo json_encode($results);
?>
