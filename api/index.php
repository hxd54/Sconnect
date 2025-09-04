<?php
session_start();

// Check if database is set up
$database_ready = false;
$service_providers = [];
$job_postings = [];

try {
    require_once __DIR__ . '/inc/db.php';
    $result = $pdo->query("SHOW TABLES LIKE 'job_categories'");
    $database_ready = $result->rowCount() > 0;

    if ($database_ready) {
        // Fetch top-rated service providers (job_provider users)
        $providers_query = $pdo->prepare("
            SELECT u.*, COUNT(r.id) as review_count
            FROM users u
            LEFT JOIN reviews r ON u.id = r.reviewed_id
            WHERE u.role = 'job_provider'
            GROUP BY u.id
            ORDER BY u.rating DESC, review_count DESC
            LIMIT 3
        ");
        $providers_query->execute();
        $service_providers = $providers_query->fetchAll();

        // Fetch recent job postings from job seekers
        $jobs_query = $pdo->prepare("
            SELECT jp.*, u.name as company_name, u.location as company_location
            FROM job_postings jp
            JOIN users u ON jp.user_id = u.id
            WHERE jp.status = 'open'
            ORDER BY jp.created_at DESC
            LIMIT 3
        ");
        $jobs_query->execute();
        $job_postings = $jobs_query->fetchAll();
    }
} catch (Exception $e) {
    $database_ready = false;
}

// If database is not ready, redirect to setup
if (!$database_ready) {
    header('Location: setup_database.php');
    exit;
}

// Check for logout message
$logout_message = isset($_GET['logout']) ? 'You have been successfully logged out.' : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sconnect – Connect with Professionals</title>
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
      background: var(--secondary);
      color: var(--text);
      line-height: 1.6;
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
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 2rem 1rem;
      background: var(--gradient);
      position: relative;
      overflow: hidden;
    }
    
    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.1)" points="0,1000 1000,0 1000,1000"/></svg>');
      background-size: cover;
    }
    
    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
    }
    
    .hero h1 {
      font-size: clamp(2.5rem, 5vw, 4rem);
      font-weight: 900;
      margin-bottom: 1.5rem;
      color: white;
      text-shadow: 0 2px 10px rgba(0,0,0,0.1);
      animation: fadeInUp 1s ease;
    }
    
    .hero p {
      font-size: clamp(1.1rem, 2.5vw, 1.3rem);
      color: rgba(255,255,255,0.9);
      margin-bottom: 3rem;
      max-width: 600px;
      animation: fadeInUp 1s ease 0.2s both;
    }
    
    .hero .actions {
      display: flex;
      gap: 1.5rem;
      justify-content: center;
      flex-wrap: wrap;
      animation: fadeInUp 1s ease 0.4s both;
    }
    
    .hero .actions a {
      background: white;
      color: var(--primary);
      padding: 1rem 2rem;
      border-radius: 50px;
      font-size: 1.1rem;
      font-weight: 600;
      text-decoration: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .hero .actions a:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    
    .hero .actions a.secondary {
      background: transparent;
      color: white;
      border: 2px solid white;
    }
    
    .hero .actions a.secondary:hover {
      background: white;
      color: var(--primary);
    }
    
    /* Sections */
    .section {
      max-width: 1200px;
      margin: 0 auto;
      padding: 4rem 2rem;
    }
    
    .section h2 {
      font-size: clamp(2rem, 4vw, 2.5rem);
      text-align: center;
      margin-bottom: 3rem;
      color: var(--text);
      font-weight: 800;
    }
    
    /* How it works */
    .how-steps {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-top: 3rem;
    }
    
    .how-step {
      background: white;
      border-radius: var(--radius);
      padding: 2rem;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .how-step::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--gradient);
    }
    
    .how-step:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .how-step i {
      font-size: 3rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }
    
    .how-step h3 {
      color: var(--primary);
      margin-bottom: 1rem;
      font-size: 1.3rem;
      font-weight: 700;
    }
    
    /* Cards */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 2rem;
      margin-top: 3rem;
    }
    
    .card {
      background: white;
      border-radius: var(--radius);
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--gradient-light);
    }
    
    .card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .card .avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin: 0 auto 1.5rem;
      object-fit: cover;
      border: 4px solid var(--primary);
      box-shadow: 0 4px 15px rgba(79,70,229,0.3);
    }
    
    .card .name {
      font-weight: 700;
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
      color: var(--text);
    }
    
    .card .role {
      font-size: 1rem;
      color: var(--muted);
      margin-bottom: 1rem;
      font-weight: 500;
    }
    
    .card .stars {
      color: var(--accent);
      font-size: 1.2rem;
      margin-bottom: 1rem;
    }
    
    .card .desc {
      font-size: 0.95rem;
      color: var(--muted);
      line-height: 1.6;
    }
    
    /* Testimonials */
    .testimonial-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-top: 3rem;
    }
    
    .testimonial {
      background: white;
      border-radius: var(--radius);
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .testimonial:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .testimonial .avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin: 0 auto 1rem;
      object-fit: cover;
      border: 3px solid var(--accent);
    }
    
    .testimonial .name {
      font-weight: 600;
      margin-bottom: 0.3rem;
      color: var(--text);
    }
    
    .testimonial .role {
      color: var(--muted);
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }
    
    .testimonial .quote {
      font-style: italic;
      color: var(--text);
      line-height: 1.6;
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
    
    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
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
        min-height: 80vh;
        padding: 1rem;
      }
      
      .hero .actions {
        flex-direction: column;
        width: 100%;
        max-width: 300px;
      }
      
      .hero .actions a {
        width: 100%;
        justify-content: center;
      }
      
      .section {
        padding: 2rem 1rem;
      }
      
      .how-steps,
      .cards,
      .testimonial-cards {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }
      
      .card,
      .how-step,
      .testimonial {
        margin: 0 auto;
        max-width: 400px;
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
      
      .section h2 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">Sconnect</div>
    <nav>
      <a href="browse_jobs.php">
        <i class="fas fa-briefcase"></i>
        Browse Jobs
      </a>
      <a href="search_talent.php">
        <i class="fas fa-users"></i>
        Find Talent
      </a>
      <a href="signup.php">Join Now</a>
      <a href="login.php" class="cta">
        <i class="fas fa-sign-in-alt"></i>
        Log In
      </a>
    </nav>
  </header>

  <?php if ($logout_message): ?>
    <div style="background: #d4edda; color: #155724; padding: 1rem; text-align: center; border-bottom: 1px solid #c3e6cb;">
      <i class="fas fa-check-circle"></i> <?= htmlspecialchars($logout_message) ?>
    </div>
  <?php endif; ?>

  <section class="hero">
    <div class="hero-content">
      <h1>Connect. Collaborate. Succeed.</h1>
      <p>Sconnect is the premier job marketplace connecting talented professionals with businesses and individuals who need their expertise. Find work, hire talent, and build lasting professional relationships.</p>
      <div class="actions">
        <a href="browse_jobs.php">
          <i class="fas fa-briefcase"></i>
          Find Jobs
        </a>
        <a href="signup.php" class="secondary">
          <i class="fas fa-user-plus"></i>
          Join Now
        </a>
      </div>
    </div>
  </section>
  
  <section class="section">
    <h2>How it works</h2>
    <div class="how-steps">
      <div class="how-step">
        <i class="fas fa-user-plus"></i>
        <h3>1. Join the Platform</h3>
        <p>Register as a Job Seeker to find opportunities or as a Job Provider to hire talent. Build your professional profile with skills and portfolio.</p>
      </div>
      <div class="how-step">
        <i class="fas fa-search"></i>
        <h3>2. Discover & Connect</h3>
        <p>Browse job opportunities or search for skilled professionals. Filter by skills, location, budget, and ratings to find the perfect match.</p>
      </div>
      <div class="how-step">
        <i class="fas fa-handshake"></i>
        <h3>3. Collaborate & Succeed</h3>
        <p>Communicate through our messaging system, share files, and work together to complete projects successfully.</p>
      </div>
    </div>
  </section>
  
  <?php if (!empty($service_providers)): ?>
  <section class="section">
    <h2>Top-rated Service Providers</h2>
    <div class="cards">
      <?php foreach ($service_providers as $provider): ?>
        <div class="card">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($provider['name']) ?>&background=4f46e5&color=fff&size=80" class="avatar" alt="Provider Avatar">
          <div class="name"><?= htmlspecialchars($provider['name']) ?></div>
          <div class="role"><?= htmlspecialchars($provider['profession'] ?: 'Service Provider') ?></div>
          <div class="stars">
            <?php
            $rating = (float)$provider['rating'];
            for ($i = 1; $i <= 5; $i++) {
              if ($i <= $rating) {
                echo '★';
              } elseif ($i - 0.5 <= $rating) {
                echo '☆';
              } else {
                echo '☆';
              }
            }
            ?>
            <span style="color: #64748b; font-size: 0.9rem; margin-left: 0.5rem;">
              (<?= number_format($rating, 1) ?>/5.0)
            </span>
          </div>
          <div class="desc">
            <?php if ($provider['bio']): ?>
              <?= htmlspecialchars(substr($provider['bio'], 0, 120)) ?><?= strlen($provider['bio']) > 120 ? '...' : '' ?>
            <?php else: ?>
              Professional service provider offering quality solutions.
              <?php if ($provider['location']): ?>
                Based in <?= htmlspecialchars($provider['location']) ?>.
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <div style="margin-top: 1rem;">
            <a href="chat.php?to=<?= $provider['id'] ?>" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.9rem;">
              <i class="fas fa-comment"></i> Contact
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
  
  <?php if (!empty($job_postings)): ?>
  <section class="section testimonials">
    <h2>Latest Job Opportunities</h2>
    <div class="testimonial-cards">
      <?php foreach ($job_postings as $job): ?>
        <div class="testimonial" style="text-align: left;">
          <div style="display: flex; align-items: center; margin-bottom: 1rem;">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($job['company_name']) ?>&background=059669&color=fff&size=60" class="avatar" alt="Company Avatar">
            <div style="margin-left: 1rem;">
              <div class="name"><?= htmlspecialchars($job['company_name']) ?></div>
              <div class="role" style="color: #059669; font-weight: 600;">
                <?= ucfirst(str_replace('_', ' ', $job['job_type'])) ?> Position
              </div>
            </div>
          </div>
          <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #059669;">
            <h4 style="margin: 0 0 0.5rem 0; color: #1e293b; font-size: 1.1rem;">
              <?= htmlspecialchars($job['title']) ?>
            </h4>
            <div class="quote" style="color: #64748b; font-style: normal; line-height: 1.5;">
              <?= htmlspecialchars(substr($job['description'], 0, 150)) ?><?= strlen($job['description']) > 150 ? '...' : '' ?>
            </div>
            <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
              <div style="font-size: 0.9rem; color: #64748b;">
                <?php if ($job['budget_min'] || $job['budget_max']): ?>
                  <span style="color: #059669; font-weight: 600;">
                    <i class="fas fa-dollar-sign"></i>
                    <?php if ($job['budget_min'] && $job['budget_max']): ?>
                      $<?= number_format($job['budget_min']) ?> - $<?= number_format($job['budget_max']) ?>
                    <?php elseif ($job['budget_min']): ?>
                      From $<?= number_format($job['budget_min']) ?>
                    <?php else: ?>
                      Up to $<?= number_format($job['budget_max']) ?>
                    <?php endif; ?>
                  </span>
                <?php endif; ?>
                <?php if ($job['company_location']): ?>
                  <span style="margin-left: 1rem;">
                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['company_location']) ?>
                  </span>
                <?php endif; ?>
              </div>
              <a href="browse_jobs.php" style="background: #059669; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-eye"></i> View Job
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
  
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