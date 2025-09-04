<?php
/**
 * CV Scanning Handler - Bridge between SConnect and SmartPath AI CV Analysis
 * This file handles CV upload and analysis using your existing SmartPath AI system
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['cv_file'];
$analysis_type = $_POST['analysis_type'] ?? 'analyze'; // 'analyze' or 'match'
$user_name = $_POST['user_name'] ?? 'User';
$language = $_POST['language'] ?? 'en';

// Validate file type
$allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
$file_type = mime_content_type($file['tmp_name']);

if (!in_array($file_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Please upload PDF, DOC, DOCX, or TXT files only.']);
    exit;
}

// Check file size (max 10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum size is 10MB.']);
    exit;
}

try {
    // Call SmartPath AI CV analysis
    $ai_response = callSmartPathCVAnalysis($file, $analysis_type, $user_name, $language);
    
    if ($ai_response) {
        echo json_encode([
            'success' => true,
            'analysis' => $ai_response,
            'analysis_type' => $analysis_type,
            'timestamp' => date('c')
        ]);
    } else {
        // Fallback analysis if SmartPath AI is not available
        $fallback_response = getFallbackCVAnalysis($file);
        echo json_encode([
            'success' => true,
            'analysis' => $fallback_response,
            'analysis_type' => 'fallback',
            'note' => 'SmartPath AI server not available, using basic analysis',
            'timestamp' => date('c')
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Analysis failed: ' . $e->getMessage(),
        'timestamp' => date('c')
    ]);
}

/**
 * Call SmartPath AI CV Analysis
 */
function callSmartPathCVAnalysis($file, $analysis_type, $user_name, $language) {
    $ai_endpoints = [
        'http://localhost:8000/' . $analysis_type,  // Your SmartPath AI backend
        'http://localhost:8000/analyze',            // Fallback to analyze endpoint
        'http://localhost:8000/match'               // Fallback to match endpoint
    ];
    
    foreach ($ai_endpoints as $endpoint) {
        try {
            // Prepare multipart form data
            $boundary = uniqid();
            $postData = '';
            
            // Add file
            $postData .= "--$boundary\r\n";
            $postData .= "Content-Disposition: form-data; name=\"file\"; filename=\"" . $file['name'] . "\"\r\n";
            $postData .= "Content-Type: " . mime_content_type($file['tmp_name']) . "\r\n\r\n";
            $postData .= file_get_contents($file['tmp_name']) . "\r\n";
            
            // Add additional parameters
            if ($analysis_type === 'match') {
                $postData .= "--$boundary\r\n";
                $postData .= "Content-Disposition: form-data; name=\"lang\"\r\n\r\n";
                $postData .= $language . "\r\n";
                
                $postData .= "--$boundary\r\n";
                $postData .= "Content-Disposition: form-data; name=\"user_name\"\r\n\r\n";
                $postData .= $user_name . "\r\n";
            }
            
            $postData .= "--$boundary--\r\n";
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        "Content-Type: multipart/form-data; boundary=$boundary",
                        'Accept: application/json',
                        'User-Agent: SConnect-CVScanner/1.0'
                    ],
                    'content' => $postData,
                    'timeout' => 30,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($endpoint, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                
                if ($data && !isset($data['error'])) {
                    return $data;
                }
            } else {
                error_log("SmartPath AI CV analysis failed for $endpoint");
            }
        } catch (Exception $e) {
            error_log("CV Analysis error for $endpoint: " . $e->getMessage());
            continue;
        }
    }
    
    return null;
}

/**
 * Fallback CV analysis when SmartPath AI is not available
 */
function getFallbackCVAnalysis($file) {
    $filename = $file['name'];
    $filesize = $file['size'];
    
    // Basic file analysis
    $analysis = [
        'file_info' => [
            'name' => $filename,
            'size' => $filesize,
            'type' => mime_content_type($file['tmp_name'])
        ],
        'analysis_summary' => 'Basic CV analysis completed. For detailed AI-powered analysis, please ensure SmartPath AI server is running.',
        'recommendations' => [
            'Start SmartPath AI server for detailed analysis',
            'Ensure your CV includes contact information',
            'List your skills and experience clearly',
            'Include relevant work history',
            'Add education background'
        ],
        'next_steps' => [
            'Run SmartPath AI server: python setup_and_run.py --start',
            'Upload your CV again for AI-powered analysis',
            'Get personalized job recommendations',
            'Receive skill gap analysis'
        ],
        'server_status' => 'SmartPath AI server not available'
    ];
    
    return $analysis;
}
?>
