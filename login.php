<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/background.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        if (isset($user['verified']) && !$user['verified']) {
            $errors[] = "Please verify your account before logging in.";
        } elseif (!empty($user['suspended']) && (int)$user['suspended'] === 1) {
            $errors[] = "Your account is suspended.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            if ($user['role'] === 'job_seeker') header('Location: dashboard_job_seeker.php');
            elseif ($user['role'] === 'job_provider') header('Location: dashboard_job_provider.php');
            elseif ($user['role'] === 'admin') header('Location: admin.php');
            // Legacy support for old roles
            elseif ($user['role'] === 'wager') header('Location: dashboard_wager.php');
            elseif ($user['role'] === 'student') header('Location: dashboard_student.php');
            elseif ($user['role'] === 'seeker') header('Location: dashboard_seeker.php');
            else header('Location: index.php');
            exit;
        }
    } else {
        $errors[] = "Invalid phone or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login – Sconnect</title>
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
      max-width: 450px;
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
    
    .form-group input {
      width: 100%;
      padding: 1rem 1rem 1rem 3rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
    }
    
    .form-group input:focus + i {
      color: var(--primary);
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
    
    .signup-link {
      text-align: center;
      margin-top: 2rem;
      color: var(--muted);
      font-size: 0.95rem;
    }
    
    .signup-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }
    
    .signup-link a:hover {
      color: var(--primary-dark);
    }
    
    .forgot-password {
      text-align: center;
      margin-top: 1rem;
    }
    
    .forgot-password a {
      color: var(--muted);
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.3s ease;
    }
    
    .forgot-password a:hover {
      color: var(--primary);
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
      
      .form-group input {
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
      <p>Welcome back to your professional network</p>
    </div>
    
    <h2>Sign In</h2>
    
    <?php if ($errors): ?>
      <div class="error">
        <i class="fas fa-exclamation-triangle"></i>
        <?= implode('<br>', $errors) ?>
      </div>
    <?php endif; ?>
    
    <form method="post">
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <div class="input-wrapper">
          <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
          <i class="fas fa-phone"></i>
        </div>
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <input type="password" id="password" name="password" required placeholder="Enter your password">
          <i class="fas fa-lock"></i>
        </div>
      </div>
      
      <button class="submit-btn" type="submit">
        <i class="fas fa-sign-in-alt"></i>
        Sign In
      </button>
    </form>
    
    <div class="forgot-password">
      <a href="#">Forgot your password?</a>
    </div>
    
    <div class="divider">
      <span>New to Sconnect?</span>
    </div>
    
    <div class="signup-link">
      Don't have an account? <a href="signup.php">Create one now</a>
    </div>
  </div>

  <!-- AI Chatbot Widget -->
  <?php include 'ai_chatbot_widget.php'; ?>
</body>
</html>