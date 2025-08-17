<?php
/**
 * Chatbot Integration Helper
 * Include this file to add the AI chatbot to any page
 */

function include_ai_chatbot() {
    include __DIR__ . '/../ai_chatbot_widget.php';
}

function include_cv_scanner() {
    include __DIR__ . '/../cv_scanner_widget.php';
}

function include_ai_tools() {
    include_ai_chatbot();
    include_cv_scanner();
}

function chatbot_head_includes() {
    echo '
    <!-- Font Awesome for chatbot icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chatbot meta tags -->
    <meta name="chatbot-enabled" content="true">
    ';
}

function chatbot_body_includes() {
    include_ai_chatbot();
}

// Auto-include chatbot if this file is included
if (!defined('CHATBOT_INCLUDED')) {
    define('CHATBOT_INCLUDED', true);
    
    // Add to the end of body if we're in a page context
    if (!headers_sent()) {
        ob_start();
        register_shutdown_function(function() {
            $content = ob_get_clean();
            
            // Only inject if we have HTML content
            if (strpos($content, '</body>') !== false) {
                $chatbot_widget = file_get_contents(__DIR__ . '/../ai_chatbot_widget.php');
                $content = str_replace('</body>', $chatbot_widget . '</body>', $content);
            }
            
            echo $content;
        });
    }
}
?>
