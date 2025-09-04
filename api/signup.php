<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/background.php';
$step = $_POST['role'] ?? $_GET['role'] ?? null;
$errors = [];
$success = false;

// Fetch job categories for job seekers (with error handling)
$job_categories = [];
try {
    $job_categories = $pdo->query("SELECT * FROM job_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
} catch (PDOException $e) {
    // If table doesn't exist, show error message
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $errors[] = "Database tables not found. Please run the database setup script first.";
        $errors[] = "Go to phpMyAdmin and run the 'complete_database_setup.sql' script.";
    } else {
        $errors[] = "Database error: " . $e->getMessage();
    }
}

// Simplified registration - no complex skills fetching needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $role = $_POST['role'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'] ?? null;
    $profession = $_POST['profession'] ?? null;
    $location = $_POST['location'] ?? null;
    $raw_password = trim($_POST['password'] ?? '');
    $terms_accepted = isset($_POST['terms']) && $_POST['terms'] === 'on';

    // New fields for job marketplace
    $experience_level = $_POST['experience_level'] ?? null;
    $hourly_rate = !empty($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : null;
    $availability = $_POST['availability'] ?? null;
    $bio = trim($_POST['bio'] ?? '');
    $portfolio_url = trim($_POST['portfolio_url'] ?? '');
    $linkedin_url = trim($_POST['linkedin_url'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $selected_categories = $_POST['categories'] ?? [];
    $skills_text = trim($_POST['skills_text'] ?? ''); // New simplified skills input

    // Basic validation
    if (!$name || !$email || !$phone || !$raw_password) $errors[] = "All fields are required.";
    if (strlen($raw_password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (!$terms_accepted) $errors[] = "You must accept the Terms and Conditions to continue.";

    // Role-specific validation (simplified)
    if ($role === 'job_seeker') {
        if (empty($selected_categories)) $errors[] = "Please select at least one job category.";
        if (!$experience_level) $errors[] = "Experience level is required.";

        // Validate experience level options (simplified to 3 options)
        $valid_experience = ['beginner', 'intermediate', 'experienced'];
        if ($experience_level && !in_array($experience_level, $valid_experience)) {
            $errors[] = "Invalid experience level selected.";
        }

        // Make skills optional for simpler registration
        // if (empty($selected_skills)) $errors[] = "Please select at least one skill.";

    } elseif ($role === 'job_provider') {
        if (!$profession) $errors[] = "Company/Organization name is required.";
        if (!$location) $errors[] = "Location is required.";
    }

    // Check if email or phone exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    if ($stmt->fetch()) $errors[] = "Email or phone already registered.";

    // Handle file upload for portfolio/proof
    $proof_path = null;
    if (!empty($_FILES['proof']['name']) && empty($errors)) {
        $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));

        // Check file size (max 10MB)
        if ($_FILES['proof']['size'] > 10*1024*1024) {
            $errors[] = "File too large (max 10MB).";
        }

        // Basic security check - prevent executable files
        $dangerous_extensions = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'msi', 'dmg', 'app'];
        if (in_array($ext, $dangerous_extensions)) {
            $errors[] = "Executable files are not allowed for security reasons.";
        }

        if (empty($errors)) {
            // Ensure uploads directory exists
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }

            $proof_path = "uploads/" . uniqid() . "." . $ext;
            if (!move_uploaded_file($_FILES['proof']['tmp_name'], $proof_path)) {
                $errors[] = "Failed to upload file. Please try again.";
                $proof_path = null;
            }
        }
    }

    if (empty($errors)) {
        // Hash the password
        $password = password_hash($raw_password, PASSWORD_DEFAULT);

        // Auto-verify users for now (can be changed to 0 for email verification later)
        $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, gender, profession, location, password, proof_path, verified, experience_level, hourly_rate, availability, bio, portfolio_url, linkedin_url, github_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $role, $name, $email, $phone, $gender, $profession, $location, $password, $proof_path,
            $experience_level, $hourly_rate, $availability, $bio ?: null, $portfolio_url ?: null,
            $linkedin_url ?: null, $github_url ?: null
        ]);
        $user_id = $pdo->lastInsertId();

        // Save job categories for job seekers
        if ($role === 'job_seeker' && !empty($selected_categories)) {
            foreach ($selected_categories as $category_id) {
                $pdo->prepare("INSERT INTO user_job_categories (user_id, category_id) VALUES (?, ?)")
                    ->execute([$user_id, (int)$category_id]);
            }
        }

        // Save skills from text input (simplified approach)
        if (!empty($skills_text)) {
            // Split skills by comma and create/link them
            $skills_array = array_map('trim', explode(',', $skills_text));
            foreach ($skills_array as $skill_name) {
                if (!empty($skill_name)) {
                    // Check if skill exists, if not create it
                    $stmt = $pdo->prepare("SELECT id FROM skills WHERE name = ?");
                    $stmt->execute([$skill_name]);
                    $skill = $stmt->fetch();

                    if (!$skill) {
                        // Create new skill
                        $stmt = $pdo->prepare("INSERT INTO skills (name) VALUES (?)");
                        $stmt->execute([$skill_name]);
                        $skill_id = $pdo->lastInsertId();
                    } else {
                        $skill_id = $skill['id'];
                    }

                    // Link skill to user
                    $pdo->prepare("INSERT IGNORE INTO user_skills (user_id, skill_id) VALUES (?, ?)")
                        ->execute([$user_id, $skill_id]);
                }
            }
        }

        // Email verification (optional - skip if table doesn't exist)
        try {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 day'));
            $pdo->prepare("INSERT INTO email_tokens (user_id, token, type, expires_at) VALUES (?, ?, 'verify', ?)")
                ->execute([$user_id, $token, $expires]);
            // TODO: Send email with link verify.php?token=$token
        } catch (PDOException $e) {
            // Skip email verification if table doesn't exist
            // This allows the system to work without email verification
        }

        // Auto-login after signup
        session_start();
        $_SESSION['user_id'] = $user_id;
        // Redirect based on role
        if ($role === 'job_seeker') header('Location: dashboard_job_seeker.php');
        elseif ($role === 'job_provider') header('Location: dashboard_job_provider.php');
        else header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up – Sconnect</title>
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
      --error: #dc2626;
      --success: #16a34a;
    }
    
    * {
      box-sizing: border-box;
    }
    
    body {
      margin: 0;
      font-family: 'Inter', 'Roboto', Arial, sans-serif;
      background: var(--gradient);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      position: relative;
      overflow-x: hidden;
    }
    
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.1)" points="0,1000 1000,0 1000,1000"/></svg>');
      background-size: cover;
      z-index: 1;
    }
    
    .container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.1);
      padding: 3rem 2.5rem;
      width: 100%;
      max-width: 500px;
      position: relative;
      z-index: 2;
      animation: slideUp 0.6s ease;
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
    
    .logo {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .logo h1 {
      font-size: 2rem;
      font-weight: 800;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0;
      letter-spacing: -0.5px;
    }
    
    .logo p {
      color: var(--muted);
      margin: 0.5rem 0 0 0;
      font-size: 0.95rem;
    }
    
    h2 {
      text-align: center;
      color: var(--text);
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 2rem;
    }
    
    /* Role Selection */
    .roles {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 1rem;
      margin: 2rem 0;
    }
    
    .role-btn {
      padding: 1.5rem 1rem;
      border-radius: 16px;
      border: 2px solid #e2e8f0;
      background: white;
      color: var(--text);
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
      font-size: 0.95rem;
    }
    
    .role-btn:hover {
      border-color: var(--primary);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79,70,229,0.15);
    }
    
    .role-btn i {
      font-size: 2rem;
      color: var(--primary);
    }
    
    .role-btn.selected {
      background: var(--gradient);
      color: white;
      border-color: var(--primary);
    }
    
    .role-btn.selected i {
      color: white;
    }
    
    /* Form Styles */
    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }
    
    .form-group label {
      display: block;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }
    
    .input-wrapper {
      position: relative;
    }
    
    .input-wrapper i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      font-size: 1.1rem;
    }
    
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 1rem 1rem 1rem 3rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
    }
    
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
    }
    
    .form-group input:focus + i {
      color: var(--primary);
    }
    
    .form-group input[type="file"] {
      padding: 0.8rem 1rem;
      padding-left: 3rem;
    }
    
    .form-group input[type="file"]::-webkit-file-upload-button {
      background: var(--primary);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      cursor: pointer;
      margin-right: 1rem;
    }
    
    /* Terms and Conditions */
    .terms-group {
      margin: 1.5rem 0;
      padding: 1rem;
      background: #f8fafc;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
    }
    
    .terms-checkbox {
      display: flex;
      align-items: flex-start;
      gap: 0.8rem;
      cursor: pointer;
    }
    
    .terms-checkbox input[type="checkbox"] {
      width: auto;
      margin: 0;
      padding: 0;
      margin-top: 0.2rem;
    }
    
    .terms-checkbox label {
      margin: 0;
      font-size: 0.9rem;
      line-height: 1.5;
      cursor: pointer;
    }
    
    .terms-checkbox a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
    }
    
    .terms-checkbox a:hover {
      text-decoration: underline;
    }
    
    .submit-btn {
      width: 100%;
      background: var(--gradient);
      color: white;
      border: none;
      font-weight: 600;
      border-radius: 12px;
      padding: 1rem;
      font-size: 1.1rem;
      cursor: pointer;
      margin-top: 1rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .submit-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .submit-btn:hover::before {
      left: 100%;
    }
    
    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79,70,229,0.3);
    }
    
    .submit-btn:active {
      transform: translateY(0);
    }
    
    .file-info {
      margin-top: 0.5rem;
      padding: 0.8rem;
      background: #f0f9ff;
      border: 1px solid #bae6fd;
      border-radius: 8px;
    }
    
    .file-info small {
      color: var(--muted);
      font-size: 0.85rem;
      line-height: 1.4;
    }
    
    .file-info i {
      color: var(--primary);
      margin-right: 0.3rem;
    }
    
    .error {
      background: #fef2f2;
      color: var(--error);
      border: 1px solid #fecaca;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .error i {
      font-size: 1.1rem;
    }
    
    .success {
      background: #f0fdf4;
      color: var(--success);
      border: 1px solid #bbf7d0;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .success i {
      font-size: 1.1rem;
    }
    
    .divider {
      text-align: center;
      margin: 2rem 0;
      position: relative;
      color: var(--muted);
      font-size: 0.9rem;
    }
    
    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background: #e2e8f0;
    }
    
    .divider span {
      background: rgba(255, 255, 255, 0.95);
      padding: 0 1rem;
      position: relative;
    }
    
    .login-link {
      text-align: center;
      margin-top: 2rem;
      color: var(--muted);
      font-size: 0.95rem;
    }
    
    .login-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }
    
    .login-link a:hover {
      color: var(--primary-dark);
    }
    
    /* Mobile Responsive */
    @media (max-width: 480px) {
      .container {
        padding: 2rem 1.5rem;
        margin: 1rem;
      }
      
      .logo h1 {
        font-size: 1.8rem;
      }
      
      h2 {
        font-size: 1.3rem;
      }
      
      .roles {
        grid-template-columns: 1fr;
      }
      
      .form-group input,
      .form-group select {
        padding: 0.9rem 0.9rem 0.9rem 2.5rem;
      }
      
      .input-wrapper i {
        left: 0.8rem;
        font-size: 1rem;
      }
    }
    
    @media (max-width: 360px) {
      .container {
        padding: 1.5rem 1rem;
      }
      
      .logo h1 {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<body>
  <?php auth_background(); ?>

  <div class="container">
    <div class="logo">
      <h1>Sconnect</h1>
      <p>Join our professional community</p>
    </div>
    
    <h2>Create Account</h2>
    
    <?php if ($success): ?>
      <div class="success">
        <i class="fas fa-check-circle"></i>
        Registration successful! Please check your email to verify your account. <a href="login.php">Log in</a>
      </div>
    <?php elseif ($errors): ?>
      <div class="error">
        <i class="fas fa-exclamation-triangle"></i>
        <?= implode('<br>', $errors) ?>
      </div>
    <?php endif; ?>

    <?php if (!$step): ?>
      <form method="post">
        <div class="roles">
          <button type="submit" name="role" value="job_seeker" class="role-btn">
            <i class="fas fa-user-tie"></i>
            <span>Job Seeker</span>
            <small>Looking for work</small>
          </button>
          <button type="submit" name="role" value="job_provider" class="role-btn">
            <i class="fas fa-building"></i>
            <span>Job Provider</span>
            <small>Hiring talent</small>
          </button>
        </div>
        <div style="text-align: center; margin-top: 1rem; color: #64748b;">
          <p>Choose your role to get started with Sconnect</p>
        </div>
      </form>
    <?php else: ?>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="role" value="<?= htmlspecialchars($step) ?>">
        
        <div class="form-group">
          <label for="name">Full Name</label>
          <div class="input-wrapper">
            <input type="text" id="name" name="name" required placeholder="Enter your full name">
            <i class="fas fa-user"></i>
          </div>
        </div>
        
        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrapper">
            <input type="email" id="email" name="email" required placeholder="Enter your email">
            <i class="fas fa-envelope"></i>
          </div>
        </div>
        
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <div class="input-wrapper">
            <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
            <i class="fas fa-phone"></i>
          </div>
        </div>
        
        <!-- Common fields for both roles -->
        <div class="form-group">
          <label for="location">Location</label>
          <div class="input-wrapper">
            <input type="text" id="location" name="location" placeholder="City, Country" required>
            <i class="fas fa-map-marker-alt"></i>
          </div>
        </div>

        <?php if ($step === 'job_seeker'): ?>
          <!-- Job Seeker specific fields (simplified) -->
          <div class="form-group">
            <label for="experience_level">Experience Level</label>
            <div class="input-wrapper">
              <select id="experience_level" name="experience_level" required>
                <option value="">Select Experience Level</option>
                <option value="beginner">Beginner (0-2 years)</option>
                <option value="intermediate">Intermediate (2-5 years)</option>
                <option value="experienced">Experienced (5+ years)</option>
              </select>
              <i class="fas fa-chart-line"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="bio">Brief Description (Optional)</label>
            <div class="input-wrapper">
              <textarea id="bio" name="bio" placeholder="Tell us about yourself and your skills..." rows="3"></textarea>
              <i class="fas fa-user"></i>
            </div>
          </div>

          <!-- Job Categories Selection (simplified) -->
          <div class="form-group">
            <label>Select Your Main Area of Work</label>
            <div class="input-wrapper">
              <select name="categories[]" required>
                <option value="">Choose your field</option>
                <?php
                // Show only the most common categories
                $common_categories = array_slice($job_categories, 0, 8); // First 8 categories
                foreach ($common_categories as $category):
                ?>
                  <option value="<?= $category['id'] ?>">
                    <?= htmlspecialchars($category['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <i class="fas fa-briefcase"></i>
            </div>
          </div>

          <!-- Skills as text input (simplified) -->
          <div class="form-group">
            <label for="skills_text">Your Skills (Optional)</label>
            <div class="input-wrapper">
              <input type="text" id="skills_text" name="skills_text" placeholder="e.g. Web Design, Customer Service, Data Entry">
              <i class="fas fa-tools"></i>
            </div>
            <small style="color: #64748b; font-size: 0.85rem;">Separate multiple skills with commas</small>
          </div>

          <div class="form-group">
            <label for="bio">Professional Bio - Optional</label>
            <div class="input-wrapper">
              <textarea id="bio" name="bio" placeholder="Tell us about yourself, your experience, and what makes you unique..." rows="4" style="resize: vertical;"></textarea>
              <i class="fas fa-user-edit"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="portfolio_url">Portfolio URL - Optional</label>
            <div class="input-wrapper">
              <input type="url" id="portfolio_url" name="portfolio_url" placeholder="https://yourportfolio.com">
              <i class="fas fa-globe"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="linkedin_url">LinkedIn Profile - Optional</label>
            <div class="input-wrapper">
              <input type="url" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/yourprofile">
              <i class="fab fa-linkedin"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="github_url">GitHub Profile - Optional</label>
            <div class="input-wrapper">
              <input type="url" id="github_url" name="github_url" placeholder="https://github.com/yourusername">
              <i class="fab fa-github"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="proof">Resume/Portfolio (Optional)</label>
            <div class="input-wrapper">
              <input type="file" id="proof" name="proof" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
              <i class="fas fa-file-upload"></i>
            </div>
            <div class="file-info">
              <small><i class="fas fa-info-circle"></i> Upload your resume or portfolio (PDF, Word, or Image files, max 10MB)</small>
            </div>
          </div>

        <?php elseif ($step === 'job_provider'): ?>
          <!-- Job Provider specific fields -->
          <div class="form-group">
            <label for="profession">Company/Organization Name</label>
            <div class="input-wrapper">
              <input type="text" id="profession" name="profession" placeholder="e.g. Tech Solutions Inc." required>
              <i class="fas fa-building"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="bio">Company Description - Optional</label>
            <div class="input-wrapper">
              <textarea id="bio" name="bio" placeholder="Describe your company, what you do, and what kind of talent you're looking for..." rows="4" style="resize: vertical;"></textarea>
              <i class="fas fa-info-circle"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="proof">Company Verification Document - Optional</label>
            <div class="input-wrapper">
              <input type="file" id="proof" name="proof" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
              <i class="fas fa-file-upload"></i>
            </div>
            <div class="file-info">
              <small><i class="fas fa-info-circle"></i> Business license, registration, or other verification documents (max 10MB)</small>
            </div>
          </div>
        <?php endif; ?>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" required placeholder="Create a strong password">
            <i class="fas fa-lock"></i>
          </div>
        </div>
        
        <div class="terms-group">
          <div class="terms-checkbox">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">
              I agree to the <a href="#" onclick="showTerms()">Terms and Conditions</a> and <a href="#" onclick="showPrivacy()">Privacy Policy</a>
            </label>
          </div>
        </div>
        
        <button class="submit-btn" type="submit" name="signup">
          <i class="fas fa-user-plus"></i>
          Create Account
        </button>
      </form>
    <?php endif; ?>
    
    <div class="divider">
      <span>Already have an account?</span>
    </div>
    
    <div class="login-link">
      Already have an account? <a href="login.php">Sign in here</a>
    </div>
  </div>
  
  <script>
    // Simple form validation and enhancements
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');
      if (form) {
        form.addEventListener('submit', function(e) {
          const requiredFields = form.querySelectorAll('[required]');
          let hasErrors = false;

          requiredFields.forEach(field => {
            if (!field.value.trim()) {
              field.style.borderColor = '#ef4444';
              hasErrors = true;
            } else {
              field.style.borderColor = '#d1d5db';
            }
          });

          if (hasErrors) {
            e.preventDefault();
            alert('Please fill in all required fields.');
          }
        });
      }
    });

    function showTerms() {
      alert('Terms and Conditions:\n\n1. You agree to use Sconnect responsibly and professionally.\n2. All information provided must be accurate and truthful.\n3. You will not misuse the platform or harass other users.\n4. Sconnect reserves the right to suspend accounts for violations.\n5. Your data will be handled according to our Privacy Policy.\n\nBy using Sconnect, you agree to these terms.');
    }

    function showPrivacy() {
      alert('Privacy Policy:\n\n1. We collect only necessary information for account creation and service provision.\n2. Your personal data is protected and never shared with third parties.\n3. We use industry-standard security measures to protect your information.\n4. You can request deletion of your data at any time.\n5. We may send you important updates about your account or the platform.\n\nYour privacy is important to us.');
    }
  </script>

  <!-- AI Chatbot Widget -->
  <?php include 'ai_chatbot_widget.php'; ?>
</body>
</html>