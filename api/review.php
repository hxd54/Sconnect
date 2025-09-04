<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
if ($user['role'] !== 'seeker') { 
    header('Location: login.php'); 
    exit; 
}

$wager_id = $_GET['wager_id'] ?? null;
$errors = [];
$success = false;

// Get wager details
if ($wager_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'wager'");
    $stmt->execute([$wager_id]);
    $wager = $stmt->fetch();
    if (!$wager) {
        header('Location: search.php');
        exit;
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5.";
    }
    if (strlen($comment) < 10) {
        $errors[] = "Comment must be at least 10 characters long.";
    }
    if (strlen($comment) > 500) {
        $errors[] = "Comment must be less than 500 characters.";
    }
    
    // Check if user already reviewed this wager
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE wager_id = ? AND reviewer_id = ?");
    $stmt->execute([$wager_id, $user['id']]);
    if ($stmt->fetch()) {
        $errors[] = "You have already reviewed this wager.";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (wager_id, reviewer_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$wager_id, $user['id'], $rating, $comment]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leave Review – Sconnect</title>
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
      max-width: 600px;
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
    
    .header {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .header h1 {
      font-size: 2rem;
      font-weight: 800;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0 0 0.5rem 0;
      letter-spacing: -0.5px;
    }
    
    .header p {
      color: var(--muted);
      margin: 0;
      font-size: 1rem;
    }
    
    .wager-info {
      background: #f8fafc;
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      border: 1px solid #e2e8f0;
    }
    
    .wager-info h3 {
      color: var(--primary);
      margin: 0 0 0.5rem 0;
      font-size: 1.2rem;
    }
    
    .wager-info p {
      color: var(--muted);
      margin: 0;
      font-size: 0.95rem;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-group label {
      display: block;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }
    
    .rating-group {
      display: flex;
      gap: 0.5rem;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .star-rating {
      display: flex;
      gap: 0.3rem;
    }
    
    .star-rating input[type="radio"] {
      display: none;
    }
    
    .star-rating label {
      font-size: 2rem;
      color: #e2e8f0;
      cursor: pointer;
      transition: color 0.2s ease;
      margin: 0;
    }
    
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input[type="radio"]:checked ~ label {
      color: var(--accent);
    }
    
    .rating-text {
      margin-left: 1rem;
      font-weight: 600;
      color: var(--text);
      min-width: 80px;
    }
    
    .form-group textarea {
      width: 100%;
      padding: 1rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 1rem;
      font-family: inherit;
      resize: vertical;
      min-height: 120px;
      transition: all 0.3s ease;
    }
    
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
    }
    
    .char-count {
      text-align: right;
      font-size: 0.85rem;
      color: var(--muted);
      margin-top: 0.5rem;
    }
    
    .char-count.limit {
      color: var(--error);
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
    
    .back-link {
      text-align: center;
      margin-top: 2rem;
    }
    
    .back-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }
    
    .back-link a:hover {
      color: var(--primary-dark);
    }
    
    /* Mobile Responsive */
    @media (max-width: 480px) {
      .container {
        padding: 2rem 1.5rem;
        margin: 1rem;
      }
      
      .header h1 {
        font-size: 1.8rem;
      }
      
      .star-rating label {
        font-size: 1.8rem;
      }
      
      .rating-group {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Leave a Review</h1>
      <p>Share your experience with this professional</p>
    </div>
    
    <?php if ($success): ?>
      <div class="success">
        <i class="fas fa-check-circle"></i>
        Review submitted successfully! Thank you for your feedback.
      </div>
      <div class="back-link">
        <a href="profile.php?id=<?= $wager_id ?>">← Back to Profile</a>
      </div>
    <?php else: ?>
      <div class="wager-info">
        <h3><?= htmlspecialchars($wager['name']) ?></h3>
        <p><?= htmlspecialchars($wager['profession']) ?> • <?= htmlspecialchars($wager['location']) ?></p>
      </div>
      
      <?php if ($errors): ?>
        <div class="error">
          <i class="fas fa-exclamation-triangle"></i>
          <?= implode('<br>', $errors) ?>
        </div>
      <?php endif; ?>
      
      <form method="post">
        <div class="form-group">
          <label>Rating</label>
          <div class="rating-group">
            <div class="star-rating">
              <input type="radio" name="rating" value="5" id="star5">
              <label for="star5"><i class="fas fa-star"></i></label>
              <input type="radio" name="rating" value="4" id="star4">
              <label for="star4"><i class="fas fa-star"></i></label>
              <input type="radio" name="rating" value="3" id="star3">
              <label for="star3"><i class="fas fa-star"></i></label>
              <input type="radio" name="rating" value="2" id="star2">
              <label for="star2"><i class="fas fa-star"></i></label>
              <input type="radio" name="rating" value="1" id="star1">
              <label for="star1"><i class="fas fa-star"></i></label>
            </div>
            <div class="rating-text" id="rating-text">Select rating</div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="comment">Your Review</label>
          <textarea id="comment" name="comment" placeholder="Share your experience with this professional (minimum 10 characters)" required></textarea>
          <div class="char-count" id="char-count">0/500 characters</div>
        </div>
        
        <button class="submit-btn" type="submit" name="submit_review">
          <i class="fas fa-paper-plane"></i>
          Submit Review
        </button>
      </form>
      
      <div class="back-link">
        <a href="profile.php?id=<?= $wager_id ?>">← Back to Profile</a>
      </div>
    <?php endif; ?>
  </div>
  
  <script>
    // Star rating functionality
    const stars = document.querySelectorAll('.star-rating input');
    const ratingText = document.getElementById('rating-text');
    const ratingLabels = {
      1: 'Poor',
      2: 'Fair', 
      3: 'Good',
      4: 'Very Good',
      5: 'Excellent'
    };
    
    stars.forEach(star => {
      star.addEventListener('change', function() {
        const rating = this.value;
        ratingText.textContent = ratingLabels[rating];
      });
    });
    
    // Character count
    const comment = document.getElementById('comment');
    const charCount = document.getElementById('char-count');
    
    comment.addEventListener('input', function() {
      const length = this.value.length;
      charCount.textContent = `${length}/500 characters`;
      
      if (length > 450) {
        charCount.classList.add('limit');
      } else {
        charCount.classList.remove('limit');
      }
    });
  </script>
</body>
</html> 