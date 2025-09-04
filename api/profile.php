<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/components.php';
require_once __DIR__ . '/inc/auth.php';

$id = $_GET['id'] ?? null;
if (!$id) { echo "Profile not found."; exit; }
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { echo "Profile not found."; exit; }

// Check if current user is logged in and is a seeker
$current_user = null;
$can_review = false;
if (is_logged_in()) {
    $current_user = current_user();
    $can_review = ($current_user['role'] === 'seeker' && $user['role'] === 'wager');
    
    // Check if user already reviewed this wager
    if ($can_review) {
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE wager_id = ? AND reviewer_id = ?");
        $stmt->execute([$user['id'], $current_user['id']]);
        $can_review = !$stmt->fetch(); // Can only review if not already reviewed
    }
}

// Fetch skills
$skills = [];
$stmt = $pdo->prepare("SELECT s.name FROM skills s JOIN user_skills us ON s.id=us.skill_id WHERE us.user_id=?");
$stmt->execute([$user['id']]);
while ($row = $stmt->fetch()) $skills[] = $row['name'];

// Fetch portfolio
$stmt = $pdo->prepare("SELECT * FROM portfolio WHERE user_id=?");
$stmt->execute([$user['id']]);
$portfolio = $stmt->fetchAll();

// Fetch reviews
$stmt = $pdo->prepare("SELECT r.*, u.name as reviewer FROM reviews r JOIN users u ON r.reviewer_id=u.id WHERE r.wager_id=? ORDER BY r.created_at DESC");
$stmt->execute([$user['id']]);
$reviews = $stmt->fetchAll();
$stmt = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE wager_id=?");
$stmt->execute([$user['id']]);
$avg = round($stmt->fetchColumn(), 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($user['name']) ?> – Sconnect Profile</title>
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
      padding: 2rem 1rem;
    }
    
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.1);
      padding: 3rem 2.5rem;
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
    
    .profile {
      display: flex;
      gap: 2rem;
      align-items: flex-start;
      margin-bottom: 2rem;
      padding-bottom: 2rem;
      border-bottom: 1px solid #e2e8f0;
    }
    
    .profile-avatar {
      flex-shrink: 0;
    }
    
    .profile-avatar img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--primary);
      box-shadow: 0 8px 25px rgba(79,70,229,0.3);
    }
    
    .profile-info {
      flex: 1;
    }
    
    .profile-info h2 {
      color: var(--primary);
      font-size: 2rem;
      font-weight: 800;
      margin: 0 0 1rem 0;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .badge {
      background: var(--accent);
      color: white;
      border-radius: 20px;
      padding: 0.3rem 1rem;
      font-size: 0.9rem;
      font-weight: 600;
    }
    
    .profile-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .detail-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--text);
    }
    
    .detail-item i {
      color: var(--primary);
      width: 20px;
    }
    
    .rating-display {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.1rem;
      font-weight: 600;
    }
    
    .rating-display .stars {
      color: var(--accent);
      font-size: 1.2rem;
    }
    
    .action-buttons {
      display: flex;
      gap: 1rem;
      margin: 2rem 0;
      flex-wrap: wrap;
    }
    
    .btn {
      background: var(--gradient);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 0.8rem 1.5rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79,70,229,0.3);
    }
    
    .btn.secondary {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
    }
    
    .btn.secondary:hover {
      background: var(--primary);
      color: white;
    }
    
    .section {
      margin: 2rem 0;
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
      color: var(--accent);
    }
    
    .portfolio-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
    }
    
    .portfolio-item {
      background: #f8fafc;
      border-radius: 16px;
      padding: 1.5rem;
      text-align: center;
      transition: all 0.3s ease;
      border: 1px solid #e2e8f0;
    }
    
    .portfolio-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .portfolio-item img {
      width: 100%;
      height: 120px;
      border-radius: 12px;
      object-fit: cover;
      margin-bottom: 1rem;
    }
    
    .portfolio-item .title {
      font-weight: 600;
      color: var(--text);
      margin-bottom: 0.5rem;
    }
    
    .portfolio-item .description {
      color: var(--muted);
      font-size: 0.9rem;
    }
    
    .reviews-section {
      margin-top: 3rem;
    }
    
    .reviews-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    
    .reviews-count {
      color: var(--muted);
      font-size: 0.95rem;
    }
    
    .reviews-list {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }
    
    .review-item {
      background: #f8fafc;
      border-radius: 16px;
      padding: 1.5rem;
      border: 1px solid #e2e8f0;
    }
    
    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .reviewer-name {
      font-weight: 600;
      color: var(--text);
    }
    
    .review-rating {
      color: var(--accent);
      font-size: 1.1rem;
    }
    
    .review-date {
      color: var(--muted);
      font-size: 0.85rem;
    }
    
    .review-comment {
      color: var(--text);
      line-height: 1.6;
    }
    
    .no-reviews {
      text-align: center;
      color: var(--muted);
      padding: 2rem;
      font-style: italic;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
      .container {
        padding: 2rem 1.5rem;
        margin: 1rem;
      }
      
      .profile {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }
      
      .profile-info h2 {
        font-size: 1.8rem;
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .profile-details {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      .btn {
        justify-content: center;
      }
      
      .portfolio-list {
        grid-template-columns: 1fr;
      }
      
      .reviews-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="profile">
      <div class="profile-avatar">
        <img src="https://randomuser.me/api/portraits/<?= $user['gender'] === 'Female' ? 'women' : 'men' ?>/<?= $user['id'] ?>.jpg" alt="<?= htmlspecialchars($user['name']) ?>">
      </div>
      <div class="profile-info">
        <h2>
          <?= htmlspecialchars($user['name']) ?>
          <?php if ($user['is_mentor']): ?><span class="badge">Mentor</span><?php endif; ?>
        </h2>
        <div class="profile-details">
          <div class="detail-item">
            <i class="fas fa-briefcase"></i>
            <span><strong>Profession:</strong> <?= htmlspecialchars($user['profession']) ?></span>
          </div>
          <div class="detail-item">
            <i class="fas fa-tags"></i>
            <span><strong>Skills:</strong> <?= implode(', ', $skills) ?></span>
          </div>
          <div class="detail-item">
            <i class="fas fa-map-marker-alt"></i>
            <span><strong>Location:</strong> <?= htmlspecialchars($user['location']) ?></span>
          </div>
          <div class="detail-item">
            <i class="fas fa-venus-mars"></i>
            <span><strong>Gender:</strong> <?= htmlspecialchars($user['gender']) ?></span>
          </div>
        </div>
        <div class="rating-display">
          <span class="stars"><?= render_star_rating($avg) ?></span>
          <span>(<?= $avg ?> average rating)</span>
        </div>
      </div>
    </div>
    
    <div class="action-buttons">
      <a href="chat.php?to=<?= $user['id'] ?>" class="btn">
        <i class="fas fa-comments"></i>
        Contact / Chat
      </a>
      <?php if ($can_review): ?>
        <a href="review.php?wager_id=<?= $user['id'] ?>" class="btn secondary">
          <i class="fas fa-star"></i>
          Leave Review
        </a>
      <?php endif; ?>
    </div>
    
    <?php if (!empty($portfolio)): ?>
      <div class="section">
        <h3><i class="fas fa-images"></i> Portfolio</h3>
        <div class="portfolio-list">
          <?php foreach ($portfolio as $item): ?>
            <div class="portfolio-item">
              <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Work">
              <div class="title"><?= htmlspecialchars($item['title']) ?></div>
              <?php if ($item['description']): ?>
                <div class="description"><?= htmlspecialchars($item['description']) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    
    <div class="reviews-section">
      <div class="reviews-header">
        <h3><i class="fas fa-star"></i> Reviews</h3>
        <div class="reviews-count"><?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?></div>
      </div>
      
      <?php if (empty($reviews)): ?>
        <div class="no-reviews">
          <i class="fas fa-star" style="font-size: 2rem; color: var(--muted); margin-bottom: 1rem;"></i>
          <p>No reviews yet. Be the first to review this professional!</p>
        </div>
      <?php else: ?>
        <div class="reviews-list">
          <?php foreach ($reviews as $r): ?>
            <div class="review-item">
              <div class="review-header">
                <div>
                  <div class="reviewer-name"><?= htmlspecialchars($r['reviewer']) ?></div>
                  <div class="review-date"><?= date('M j, Y', strtotime($r['created_at'])) ?></div>
                </div>
                <div class="review-rating"><?= render_star_rating($r['rating']) ?></div>
              </div>
              <div class="review-comment"><?= htmlspecialchars($r['comment']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>