<?php
/**
 * AI Chat Handler - Bridge between SConnect and SmartPath AI
 * This file handles communication with the SmartPath AI backend
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

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing message parameter']);
    exit;
}

$message = trim($input['message']);
$session_id = $input['session_id'] ?? null;
$user_name = $input['user_name'] ?? 'User';
$timestamp = $input['timestamp'] ?? date('c');

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message cannot be empty']);
    exit;
}

try {
    // Try to connect to SmartPath AI backend
    $ai_response = callSmartPathAI($message, $session_id, $user_name);
    
    if ($ai_response) {
        echo json_encode([
            'success' => true,
            'ai_response' => $ai_response,
            'session_id' => $session_id,
            'timestamp' => $timestamp,
            'user_name' => $user_name
        ]);
    } else {
        // Fallback response if AI is not available
        $fallback_response = getFallbackResponse($message);
        echo json_encode([
            'success' => true,
            'ai_response' => $fallback_response,
            'session_id' => $session_id,
            'timestamp' => $timestamp,
            'user_name' => $user_name,
            'fallback' => true
        ]);
    }
    
} catch (Exception $e) {
    error_log("AI Chat Handler Error: " . $e->getMessage());
    
    // Return fallback response
    $fallback_response = getFallbackResponse($message);
    echo json_encode([
        'success' => true,
        'ai_response' => $fallback_response,
        'session_id' => $session_id,
        'timestamp' => $timestamp,
        'user_name' => $user_name,
        'fallback' => true
    ]);
}

/**
 * Call SmartPath AI backend
 */
function callSmartPathAI($message, $session_id, $user_name) {
    $ai_endpoints = [
        'http://localhost:8000/chat',   // Your SmartPath AI backend
        'http://localhost:8000/api/chat', // Alternative endpoint
        'http://localhost:51690/chat'   // Fallback enhanced server
    ];
    
    foreach ($ai_endpoints as $endpoint) {
        try {
            $postData = [
                'message' => $message,
                'speak_response' => false,
                'language' => 'en'
            ];
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/x-www-form-urlencoded',
                        'Accept: application/json',
                        'User-Agent: SConnect-Chatbot/1.0'
                    ],
                    'content' => http_build_query($postData),
                    'timeout' => 15,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($endpoint, false, $context);

            if ($response !== false) {
                $data = json_decode($response, true);

                if ($data && isset($data['response'])) {
                    return $data['response'];
                } elseif ($data && isset($data['ai_response'])) {
                    return $data['ai_response'];
                } elseif ($data && isset($data['message'])) {
                    return $data['message'];
                }
            } else {
                // Log the error for debugging
                $error = error_get_last();
                error_log("SmartPath AI connection failed for $endpoint: " . ($error['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            // Continue to next endpoint
            continue;
        }
    }
    
    return null; // No AI endpoint available
}

/**
 * Fallback responses when AI is not available
 */
function getFallbackResponse($message) {
    $message_lower = strtolower(trim($message));
    
    // Job-related queries
    if (preg_match('/\b(job|work|career|position|hiring|employment)\b/', $message_lower)) {
        return "I'd love to help you with job-related questions! Here are some general tips:\n\n" .
               "**Job Search Strategies:**\n" .
               "• Update your LinkedIn profile with relevant keywords\n" .
               "• Apply to 5-10 positions per week\n" .
               "• Network with professionals in your industry\n" .
               "• Customize your CV for each application\n\n" .
               "**Popular Job Boards:**\n" .
               "• LinkedIn Jobs\n" .
               "• Indeed\n" .
               "• Glassdoor\n" .
               "• Company career pages\n\n" .
               "For personalized advice, please ensure the SmartPath AI server is running!";
    }
    
    // CV/Resume queries
    if (preg_match('/\b(cv|resume|curriculum)\b/', $message_lower)) {
        return "Here are some essential CV improvement tips:\n\n" .
               "**CV Best Practices:**\n" .
               "• Keep it to 1-2 pages maximum\n" .
               "• Use clear, professional formatting\n" .
               "• Include relevant keywords from job descriptions\n" .
               "• Quantify your achievements with numbers\n" .
               "• Proofread for spelling and grammar errors\n\n" .
               "**Key Sections:**\n" .
               "• Contact information\n" .
               "• Professional summary\n" .
               "• Work experience\n" .
               "• Education\n" .
               "• Skills\n" .
               "• Certifications (if applicable)\n\n" .
               "For detailed CV analysis, please ensure the SmartPath AI server is running!";
    }
    
    // Skills development
    if (preg_match('/\b(skill|learn|develop|training|course)\b/', $message_lower)) {
        return "Skill development is crucial for career growth! Here are some recommendations:\n\n" .
               "**High-Demand Skills:**\n" .
               "• **Technical:** Python, SQL, Data Analysis, Digital Marketing\n" .
               "• **Soft Skills:** Communication, Leadership, Problem-solving\n" .
               "• **Business:** Project Management, Business Analysis\n\n" .
               "**Learning Platforms:**\n" .
               "• Coursera - University courses\n" .
               "• Udemy - Practical skills\n" .
               "• LinkedIn Learning - Professional development\n" .
               "• edX - Academic courses\n\n" .
               "**Free Resources:**\n" .
               "• YouTube tutorials\n" .
               "• Khan Academy\n" .
               "• FreeCodeCamp (for programming)\n\n" .
               "For personalized skill recommendations, please ensure the SmartPath AI server is running!";
    }
    
    // Interview preparation
    if (preg_match('/\b(interview|prepare|preparation)\b/', $message_lower)) {
        return "Interview preparation is key to success! Here's a comprehensive guide:\n\n" .
               "**Before the Interview:**\n" .
               "• Research the company thoroughly\n" .
               "• Review the job description\n" .
               "• Prepare answers to common questions\n" .
               "• Prepare questions to ask them\n" .
               "• Practice with mock interviews\n\n" .
               "**Common Questions:**\n" .
               "• Tell me about yourself\n" .
               "• Why do you want this job?\n" .
               "• What are your strengths/weaknesses?\n" .
               "• Where do you see yourself in 5 years?\n\n" .
               "**During the Interview:**\n" .
               "• Arrive 10-15 minutes early\n" .
               "• Maintain good eye contact\n" .
               "• Use the STAR method for behavioral questions\n" .
               "• Ask thoughtful questions\n\n" .
               "For personalized interview coaching, please ensure the SmartPath AI server is running!";
    }
    
    // Salary/compensation
    if (preg_match('/\b(salary|pay|compensation|money|wage)\b/', $message_lower)) {
        return "Salary negotiation is an important skill! Here's what you should know:\n\n" .
               "**Research Market Rates:**\n" .
               "• Use Glassdoor, PayScale, Salary.com\n" .
               "• Consider location and experience level\n" .
               "• Factor in benefits and perks\n\n" .
               "**Negotiation Tips:**\n" .
               "• Wait for the offer before discussing salary\n" .
               "• Present a range, not a single number\n" .
               "• Justify your request with research\n" .
               "• Consider the total compensation package\n\n" .
               "**Beyond Base Salary:**\n" .
               "• Health insurance\n" .
               "• Retirement contributions\n" .
               "• Vacation time\n" .
               "• Professional development budget\n" .
               "• Flexible work arrangements\n\n" .
               "For personalized salary advice, please ensure the SmartPath AI server is running!";
    }
    
    // Greeting responses
    if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening)\b/', $message_lower)) {
        return "Hello! I'm your AI career assistant. I'm here to help you with:\n\n" .
               "• **Job Search** - Finding the right opportunities\n" .
               "• **CV Improvement** - Making your resume stand out\n" .
               "• **Skill Development** - Learning what employers want\n" .
               "• **Interview Prep** - Acing your next interview\n" .
               "• **Career Planning** - Mapping your professional journey\n\n" .
               "What would you like to know about? Just ask me anything related to your career!\n\n" .
               "*Note: For the most personalized and detailed responses, please ensure the SmartPath AI server is running.*";
    }
    
    // Default response
    return "Thank you for your question! I'm here to help with career-related topics including:\n\n" .
           "• Job search strategies\n" .
           "• CV and resume improvement\n" .
           "• Skill development recommendations\n" .
           "• Interview preparation\n" .
           "• Career planning and advancement\n\n" .
           "Could you please rephrase your question or ask about one of these topics? I'd be happy to provide detailed guidance!\n\n" .
           "*For the most comprehensive and personalized responses, please ensure the SmartPath AI server is running on port 51690.*";
}
?>
