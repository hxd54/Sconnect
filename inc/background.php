<?php
/**
 * Professional Background System
 * Provides beautiful blue and white icon patterns for all pages
 */

function include_professional_background($type = 'default') {
    // Include the CSS file
    echo '<link rel="stylesheet" href="css/professional_background.css">';
    
    // Add the background HTML based on page type
    switch ($type) {
        case 'messaging':
            echo '<div class="messaging-background"></div>';
            echo '<div class="professional-background-overlay"></div>';
            break;
            
        case 'auth':
            echo '<div class="auth-background"></div>';
            echo '<div class="professional-background-overlay"></div>';
            break;
            
        case 'dashboard':
        case 'default':
        default:
            echo '<div class="professional-background"></div>';
            echo '<div class="professional-background-overlay"></div>';
            break;
    }
}

function add_background_styles() {
    ?>
    <style>
        /* Ensure body has proper styling for background */
        body {
            position: relative;
            overflow-x: hidden;
        }
        
        /* Make sure main content is above background */
        .main-content,
        .container,
        .header,
        .feed,
        .sidebar {
            position: relative;
            z-index: 2;
        }
        
        /* Enhance glassmorphism effects */
        .post,
        .create-post,
        .sidebar,
        .modal-content,
        .container {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }
        
        /* Special styling for header */
        .header {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
        }
        
        /* Enhance message bubbles for messaging pages */
        .message-bubble {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(15px) !important;
        }
        
        .message-bubble.sent {
            background: rgba(102, 126, 234, 0.9) !important;
            backdrop-filter: blur(15px) !important;
        }
        
        /* Auth page specific styling */
        .auth-background + * .container {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(25px) !important;
            box-shadow: 0 25px 70px rgba(0,0,0,0.15) !important;
        }
        
        /* Floating elements enhancement */
        .floating-element {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(15px) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }
        
        /* Chat widget enhancement */
        .ai-chat-widget {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }
        
        .ai-chat-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4) !important;
        }
        
        /* Ensure readability */
        .professional-background ~ * {
            position: relative;
            z-index: 1;
        }
        
        /* Animation performance optimization */
        .professional-background,
        .messaging-background,
        .auth-background {
            will-change: transform;
            transform: translateZ(0);
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .professional-background::before,
            .professional-background::after,
            .messaging-background::before,
            .auth-background::before {
                animation-duration: 40s;
            }
        }
        
        /* Reduce motion for users who prefer it */
        @media (prefers-reduced-motion: reduce) {
            .professional-background::before,
            .professional-background::after,
            .messaging-background::before,
            .auth-background::before {
                animation: none;
            }
        }
        
        /* Dark mode support (future enhancement) */
        @media (prefers-color-scheme: dark) {
            .professional-background,
            .messaging-background,
            .auth-background {
                background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
            }
        }
    </style>
    <?php
}

// Quick helper functions for specific page types
function dashboard_background() {
    include_professional_background('dashboard');
    add_background_styles();
}

function messaging_background() {
    include_professional_background('messaging');
    add_background_styles();
}

function auth_background() {
    include_professional_background('auth');
    add_background_styles();
}

// Function to add background to any existing page
function add_professional_background_to_page($page_type = 'default') {
    ob_start();
    include_professional_background($page_type);
    add_background_styles();
    $background_html = ob_get_clean();
    
    // Insert background right after <body> tag
    return function($content) use ($background_html) {
        return str_replace('<body>', '<body>' . $background_html, $content);
    };
}
?>
