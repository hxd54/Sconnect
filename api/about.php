<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About – Sconnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-dark: #3730a3;
      --secondary: #f9fafb;
      --accent: #fbbf24;
      --text: #1e293b;
      --muted: #64748b;
      --radius: 16px;
      --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-light: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    * {
      box-sizing: border-box;
    }
    
    body {
      margin: 0;
      font-family: 'Inter', 'Roboto', Arial, sans-serif;
      background: var(--gradient);
      color: var(--text);
      line-height: 1.6;
      position: relative;
      overflow-x: hidden;
    }
    
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.1)" points="0,1000 1000,0 1000,1000"/></svg>');
      background-size: cover;
      z-index: -1;
    }
    
    /* Header */
    header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 1000;
      transition: all 0.3s ease;
    }
    
    .logo {
      font-weight: 800;
      font-size: 1.8rem;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.5px;
    }
    
    nav {
      display: flex;
      align-items: center;
      gap: 2rem;
    }
    
    nav a {
      text-decoration: none;
      color: var(--text);
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
    }
    
    nav a:hover {
      color: var(--primary);
    }
    
    nav a::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 2px;
      background: var(--gradient);
      transition: width 0.3s ease;
    }
    
    nav a:hover::after {
      width: 100%;
    }
    
    nav a.cta {
      background: var(--gradient);
      color: white;
      padding: 0.8rem 1.5rem;
      border-radius: 50px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(79,70,229,0.3);
      transition: all 0.3s ease;
    }
    
    nav a.cta:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79,70,229,0.4);
    }
    
    /* Hero Section */
    .hero {
      text-align: center;
      padding: 4rem 2rem;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      margin: 2rem auto;
      max-width: 800px;
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.1);
      animation: slideUp 0.8s ease;
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .hero h1 {
      font-size: clamp(2.5rem, 5vw, 3.5rem);
      font-weight: 900;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      letter-spacing: -1px;
    }
    
    .hero p {
      font-size: clamp(1.1rem, 2.5vw, 1.3rem);
      color: var(--muted);
      max-width: 600px;
      margin: 0 auto 2rem;
      line-height: 1.7;
    }
    
    /* Main Content */
    .container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 2rem;
    }
    
    .content-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin: 3rem 0;
    }
    
    .section {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 2.5rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--gradient);
    }
    
    .section:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .section h3 {
      color: var(--primary);
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }
    
    .section h3 i {
      font-size: 1.8rem;
      color: var(--accent);
    }
    
    .section p {
      color: var(--text);
      line-height: 1.7;
      margin-bottom: 1.5rem;
      font-size: 1rem;
    }
    
    .section ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .section li {
      padding: 0.8rem 0;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      gap: 0.8rem;
      color: var(--text);
      font-size: 0.95rem;
    }
    
    .section li:last-child {
      border-bottom: none;
    }
    
    .section li i {
      color: var(--primary);
      font-size: 1rem;
      min-width: 20px;
    }
    
    /* Stats Section */
    .stats {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 3rem 2rem;
      margin: 3rem auto;
      max-width: 800px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stats h2 {
      color: var(--primary);
      font-size: 2rem;
      font-weight: 800;
      margin-bottom: 2rem;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 2rem;
    }
    
    .stat-item {
      text-align: center;
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 900;
      color: var(--primary);
      margin-bottom: 0.5rem;
    }
    
    .stat-label {
      color: var(--muted);
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    /* CTA Section */
    .cta-section {
      text-align: center;
      padding: 4rem 2rem;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 24px;
      margin: 3rem auto;
      max-width: 600px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .cta-section h2 {
      color: var(--primary);
      font-size: 2rem;
      font-weight: 800;
      margin-bottom: 1rem;
    }
    
    .cta-section p {
      color: var(--muted);
      margin-bottom: 2rem;
      font-size: 1.1rem;
    }
    
    .cta-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }
    
    .cta-btn {
      background: var(--gradient);
      color: white;
      padding: 1rem 2rem;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79,70,229,0.3);
    }
    
    .cta-btn.secondary {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
    }
    
    .cta-btn.secondary:hover {
      background: var(--primary);
      color: white;
    }
    
    /* Footer */
    footer {
      background: var(--text);
      color: white;
      text-align: center;
      padding: 3rem 2rem 2rem;
      margin-top: 4rem;
    }
    
    footer .footer-content {
      max-width: 1200px;
      margin: 0 auto;
    }
    
    footer .social-links {
      display: flex;
      justify-content: center;
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    footer .social-links a {
      color: white;
      font-size: 1.5rem;
      transition: color 0.3s ease;
    }
    
    footer .social-links a:hover {
      color: var(--accent);
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
      header {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
      }
      
      nav {
        gap: 1rem;
      }
      
      .hero {
        margin: 1rem;
        padding: 2rem 1rem;
      }
      
      .container {
        padding: 0 1rem;
      }
      
      .content-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }
      
      .section {
        padding: 2rem 1.5rem;
      }
      
      .stats {
        margin: 2rem 1rem;
        padding: 2rem 1rem;
      }
      
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
      }
      
      .cta-section {
        margin: 2rem 1rem;
        padding: 2rem 1rem;
      }
      
      .cta-buttons {
        flex-direction: column;
        align-items: center;
      }
      
      .cta-btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
      }
    }
    
    @media (max-width: 480px) {
      .logo {
        font-size: 1.5rem;
      }
      
      nav {
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .hero h1 {
        font-size: 2rem;
      }
      
      .hero p {
        font-size: 1rem;
      }
      
      .section h3 {
        font-size: 1.3rem;
      }
      
      .stats h2 {
        font-size: 1.5rem;
      }
      
      .cta-section h2 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">Sconnect</div>
    <nav>
      <a href="index.php">Home</a>
      <a href="resources.php">Resources</a>
      <a href="login.php" class="cta">
        <i class="fas fa-sign-in-alt"></i>
        Log In
      </a>
    </nav>
  </header>
  
  <div class="container">
    <div class="hero">
      <h1>About Sconnect</h1>
      <p>Connecting professionals, students, and service seekers in a trusted, transparent, and innovative platform that fosters growth, learning, and meaningful relationships.</p>
    </div>
    
    <div class="content-grid">
      <div class="section">
        <h3>
          <i class="fas fa-lightbulb"></i>
          What is Sconnect?
        </h3>
        <p>Sconnect is a revolutionary platform designed to bridge the gap between certified service providers (Wagers) and people in need of their expertise. We also create valuable opportunities for students to gain real-world experience through mentorship programs.</p>
        <p>Our mission is to build a community where professionals can showcase their skills, students can learn from experts, and service seekers can find trusted solutions.</p>
      </div>
      
      <div class="section">
        <h3>
          <i class="fas fa-cogs"></i>
          How It Works
        </h3>
        <ul>
          <li><i class="fas fa-check-circle"></i>Wagers sign up, upload credentials, and create comprehensive profiles</li>
          <li><i class="fas fa-check-circle"></i>Service seekers search and connect via advanced filters</li>
          <li><i class="fas fa-check-circle"></i>Students get mentored by experienced professionals</li>
          <li><i class="fas fa-check-circle"></i>Secure communication through our built-in chatroom</li>
          <li><i class="fas fa-check-circle"></i>Review system ensures quality and accountability</li>
        </ul>
      </div>
      
      <div class="section">
        <h3>
          <i class="fas fa-star"></i>
          Benefits
        </h3>
        <ul>
          <li><i class="fas fa-shield-alt"></i>Trust and transparency through verified credentials</li>
          <li><i class="fas fa-dollar-sign"></i>Affordable and accessible services for everyone</li>
          <li><i class="fas fa-graduation-cap"></i>Mentorship model fosters skill-building and growth</li>
          <li><i class="fas fa-star"></i>Review system ensures quality and accountability</li>
          <li><i class="fas fa-mobile-alt"></i>Mobile-friendly platform accessible anywhere</li>
        </ul>
      </div>
      
      <div class="section">
        <h3>
          <i class="fas fa-heart"></i>
          Why Choose Us
        </h3>
        <ul>
          <li><i class="fas fa-mobile-alt"></i>Mobile-friendly and intuitive design</li>
          <li><i class="fas fa-certificate"></i>Verified and rated professionals</li>
          <li><i class="fas fa-gavel"></i>Built-in legal and educational support</li>
          <li><i class="fas fa-users"></i>Community-driven platform</li>
          <li><i class="fas fa-lock"></i>Secure and private communication</li>
        </ul>
      </div>
    </div>
    
    <div class="stats">
      <h2>Platform Statistics</h2>
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-number">500+</div>
          <div class="stat-label">Verified Professionals</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">1000+</div>
          <div class="stat-label">Successful Connections</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">200+</div>
          <div class="stat-label">Student Mentorships</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">4.8★</div>
          <div class="stat-label">Average Rating</div>
        </div>
      </div>
    </div>
    
    <div class="cta-section">
      <h2>Ready to Get Started?</h2>
      <p>Join thousands of professionals, students, and service seekers who are already benefiting from Sconnect's innovative platform.</p>
      <div class="cta-buttons">
        <a href="signup.php" class="cta-btn">
          <i class="fas fa-user-plus"></i>
          Create Account
        </a>
        <a href="login.php" class="cta-btn secondary">
          <i class="fas fa-sign-in-alt"></i>
          Sign In
        </a>
      </div>
    </div>
  </div>
  
  <footer>
    <div class="footer-content">
      <div class="social-links">
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-linkedin"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
      </div>
      <p>&copy; <?php echo date('Y'); ?> Sconnect. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>