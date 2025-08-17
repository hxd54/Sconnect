<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modern Design Showcase - Sconnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #fafafa;
            margin: 0; 
            min-height: 100vh;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .showcase-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .showcase-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .showcase-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        
        .card-header {
            padding: 1.5rem;
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            color: white;
        }
        
        .card-header h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }
        
        .card-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        .feature-list {
            list-style: none;
            margin-bottom: 1.5rem;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .feature-list i {
            color: #00a400;
            width: 16px;
        }
        
        .btn {
            background: #0095f6;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
        }
        
        .btn:hover {
            background: #1877f2;
            transform: translateY(-1px);
        }
        
        .btn.secondary {
            background: #efefef;
            color: #262626;
        }
        
        .btn.secondary:hover {
            background: #dbdbdb;
        }
        
        .comparison-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 1.5rem;
        }
        
        .before-after {
            text-align: center;
        }
        
        .before-after h4 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .before-after img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .features-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .feature-card {
            padding: 1.5rem;
            border: 1px solid #dbdbdb;
            border-radius: 8px;
            text-align: center;
        }
        
        .feature-card i {
            font-size: 2rem;
            color: #0095f6;
            margin-bottom: 1rem;
        }
        
        .feature-card h4 {
            margin-bottom: 0.5rem;
            color: #262626;
        }
        
        .feature-card p {
            color: #8e8e8e;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .showcase-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .comparison-grid {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎨 Modern Sconnect Design</h1>
            <p>Instagram & Telegram-inspired interface with full mobile compatibility</p>
        </div>
        
        <div class="showcase-grid">
            <div class="showcase-card">
                <div class="card-header">
                    <h3><i class="fas fa-user-tie"></i> Job Seeker Dashboard</h3>
                    <p>Instagram-style feed with portfolio posts</p>
                </div>
                <div class="card-content">
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Instagram-style post grid</li>
                        <li><i class="fas fa-check"></i> Profile stats display</li>
                        <li><i class="fas fa-check"></i> Skills tags</li>
                        <li><i class="fas fa-check"></i> Mobile-first design</li>
                        <li><i class="fas fa-check"></i> Floating action button</li>
                        <li><i class="fas fa-check"></i> Bottom navigation</li>
                    </ul>
                    <a href="modern_dashboard_job_seeker.php" class="btn">
                        <i class="fas fa-eye"></i> View Job Seeker Dashboard
                    </a>
                </div>
            </div>
            
            <div class="showcase-card">
                <div class="card-header">
                    <h3><i class="fas fa-building"></i> Job Provider Dashboard</h3>
                    <p>Professional interface for employers</p>
                </div>
                <div class="card-content">
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Job posting cards</li>
                        <li><i class="fas fa-check"></i> Talent discovery feed</li>
                        <li><i class="fas fa-check"></i> Company profile section</li>
                        <li><i class="fas fa-check"></i> Application tracking</li>
                        <li><i class="fas fa-check"></i> Quick job posting</li>
                        <li><i class="fas fa-check"></i> Responsive design</li>
                    </ul>
                    <a href="modern_dashboard_job_provider.php" class="btn">
                        <i class="fas fa-eye"></i> View Provider Dashboard
                    </a>
                </div>
            </div>
            
            <div class="showcase-card">
                <div class="card-header">
                    <h3><i class="fas fa-comments"></i> Modern Chat Interface</h3>
                    <p>Telegram-inspired messaging system</p>
                </div>
                <div class="card-content">
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Telegram-style bubbles</li>
                        <li><i class="fas fa-check"></i> File attachments</li>
                        <li><i class="fas fa-check"></i> Real-time messaging</li>
                        <li><i class="fas fa-check"></i> Message timestamps</li>
                        <li><i class="fas fa-check"></i> Auto-scroll to bottom</li>
                        <li><i class="fas fa-check"></i> Mobile optimized</li>
                    </ul>
                    <a href="modern_chat.php?to=1" class="btn">
                        <i class="fas fa-eye"></i> View Chat Interface
                    </a>
                </div>
            </div>
            
            <div class="showcase-card">
                <div class="card-header">
                    <h3><i class="fas fa-mobile-alt"></i> Mobile Experience</h3>
                    <p>Optimized for all screen sizes</p>
                </div>
                <div class="card-content">
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Touch-friendly interface</li>
                        <li><i class="fas fa-check"></i> Swipe gestures</li>
                        <li><i class="fas fa-check"></i> Bottom navigation</li>
                        <li><i class="fas fa-check"></i> Responsive grids</li>
                        <li><i class="fas fa-check"></i> Fast loading</li>
                        <li><i class="fas fa-check"></i> PWA ready</li>
                    </ul>
                    <button class="btn" onclick="testMobile()">
                        <i class="fas fa-mobile-alt"></i> Test Mobile View
                    </button>
                </div>
            </div>
        </div>
        
        <div class="comparison-section">
            <h2>🔄 Design Evolution</h2>
            <p>From traditional web design to modern social media-inspired interface</p>
            <div class="comparison-grid">
                <div class="before-after">
                    <h4>❌ Before: Traditional Design</h4>
                    <div style="background: #f8f9fa; padding: 2rem; border-radius: 8px; border: 2px dashed #dee2e6;">
                        <p style="color: #6c757d; font-style: italic;">
                            • Complex navigation<br>
                            • Desktop-only layout<br>
                            • Limited mobile support<br>
                            • Basic styling<br>
                            • Poor user engagement
                        </p>
                    </div>
                </div>
                <div class="before-after">
                    <h4>✅ After: Modern Design</h4>
                    <div style="background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); padding: 2rem; border-radius: 8px; color: white;">
                        <p>
                            • Instagram-style interface<br>
                            • Mobile-first approach<br>
                            • Touch-friendly design<br>
                            • Modern aesthetics<br>
                            • High user engagement
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="features-section">
            <h2>🚀 Key Features</h2>
            <p>Modern design elements that enhance user experience</p>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fab fa-instagram"></i>
                    <h4>Instagram-Style Grid</h4>
                    <p>Post layouts inspired by Instagram's clean and engaging design</p>
                </div>
                <div class="feature-card">
                    <i class="fab fa-telegram"></i>
                    <h4>Telegram Chat</h4>
                    <p>Messaging interface with modern bubbles and smooth animations</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h4>Mobile First</h4>
                    <p>Designed primarily for mobile devices with desktop enhancement</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-palette"></i>
                    <h4>Modern UI</h4>
                    <p>Clean, minimalist design with smooth transitions and animations</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bolt"></i>
                    <h4>Fast Performance</h4>
                    <p>Optimized for speed with efficient loading and smooth interactions</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-heart"></i>
                    <h4>User Engagement</h4>
                    <p>Interactive elements that encourage user participation and retention</p>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <h2>🎯 Ready to Experience the New Design?</h2>
            <p style="margin: 1rem 0; color: #8e8e8e;">Choose your role to explore the modern interface</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem;">
                <a href="modern_dashboard_job_seeker.php" class="btn" style="width: auto;">
                    <i class="fas fa-user-tie"></i> Job Seeker Experience
                </a>
                <a href="modern_dashboard_job_provider.php" class="btn secondary" style="width: auto;">
                    <i class="fas fa-building"></i> Employer Experience
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function testMobile() {
            alert('💡 To test mobile view:\n\n1. Open browser developer tools (F12)\n2. Click the mobile device icon\n3. Select a mobile device\n4. Refresh the page\n\nOr simply resize your browser window to see responsive design in action!');
        }
        
        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Add entrance animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.showcase-card, .comparison-section, .features-section');
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
