<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/components.php';
require_once __DIR__ . '/inc/auth.php';

$filters = [
    'profession' => $_GET['profession'] ?? '',
    'location' => $_GET['location'] ?? '',
    'rating' => $_GET['rating'] ?? '',
    'gender' => $_GET['gender'] ?? '',
    'keywords' => $_GET['keywords'] ?? '',
    'mentor' => $_GET['mentor'] ?? '',
];

$sql = "SELECT * FROM users WHERE role = 'wager'";
$params = [];
if ($filters['profession']) { $sql .= " AND profession LIKE ?"; $params[] = "%{$filters['profession']}%"; }
if ($filters['location']) { $sql .= " AND location LIKE ?"; $params[] = "%{$filters['location']}%"; }
if ($filters['gender']) { $sql .= " AND gender = ?"; $params[] = $filters['gender']; }
if ($filters['mentor']) { $sql .= " AND is_mentor = 1"; }
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$wagers = $stmt->fetchAll();

// Check if current user can review
$current_user = null;
$can_review = false;
if (is_logged_in()) {
    $current_user = current_user();
    $can_review = ($current_user['role'] === 'seeker');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Find Wagers – Sconnect</title>
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
      max-width: 1200px;
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
    
    h2 {
      color: var(--primary);
      font-size: 2rem;
      font-weight: 800;
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .search-form {
      background: #f8fafc;
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 3rem;
      border: 1px solid #e2e8f0;
    }
    
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;
    }
    
    .form-group {
      display: flex;
      flex-direction: column;
    }
    
    .form-group label {
      font-weight: 600;
      color: var(--text);
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }
    
    .form-group input,
    .form-group select {
      padding: 0.8rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
    }
    
    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 1rem;
    }
    
    .checkbox-group input[type="checkbox"] {
      width: auto;
      margin: 0;
    }
    
    .filter-btn {
      background: var(--gradient);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 0.8rem 2rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 1rem;
    }
    
    .filter-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79,70,229,0.3);
    }
    
    .results-count {
      text-align: center;
      color: var(--muted);
      margin-bottom: 2rem;
      font-size: 1.1rem;
    }
    
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }
    
    .card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
      border: 1px solid #e2e8f0;
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
      background: var(--gradient);
    }
    
    .card:hover {
      transform: translateY(-10px);
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
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .card .badge {
      background: var(--accent);
      color: white;
      border-radius: 20px;
      padding: 0.2rem 0.8rem;
      font-size: 0.8rem;
      font-weight: 600;
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
    
    .card .location {
      font-size: 0.95rem;
      color: var(--muted);
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .card .location i {
      color: var(--primary);
    }
    
    .card-actions {
      display: flex;
      gap: 0.8rem;
      justify-content: center;
      flex-wrap: wrap;
    }
    
    .btn {
      background: var(--gradient);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 0.7rem 1.2rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      font-size: 0.9rem;
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
    
    .no-results {
      text-align: center;
      color: var(--muted);
      padding: 3rem;
      font-size: 1.1rem;
    }
    
    .no-results i {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: var(--muted);
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
      .container {
        padding: 2rem 1.5rem;
        margin: 1rem;
      }
      
      h2 {
        font-size: 1.8rem;
      }
      
      .search-form {
        padding: 1.5rem;
      }
      
      .form-row {
        grid-template-columns: 1fr;
      }
      
      .cards {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }
      
      .card {
        padding: 1.5rem;
      }
      
      .card-actions {
        flex-direction: column;
      }
      
      .btn {
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Find a Wager</h2>
    
    <div class="search-form">
      <form method="get">
        <div class="form-row">
          <div class="form-group">
            <label for="profession">Profession</label>
            <input type="text" id="profession" name="profession" placeholder="e.g. Web Developer" value="<?= htmlspecialchars($filters['profession']) ?>">
          </div>
          <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" placeholder="e.g. New York" value="<?= htmlspecialchars($filters['location']) ?>">
          </div>
          <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
              <option value="">Any Gender</option>
              <option <?= $filters['gender']=='Male'?'selected':'' ?>>Male</option>
              <option <?= $filters['gender']=='Female'?'selected':'' ?>>Female</option>
              <option <?= $filters['gender']=='Other'?'selected':'' ?>>Other</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="keywords">Keywords</label>
            <input type="text" id="keywords" name="keywords" placeholder="Search keywords" value="<?= htmlspecialchars($filters['keywords']) ?>">
          </div>
          <div class="form-group">
            <div class="checkbox-group">
              <input type="checkbox" id="mentor" name="mentor" value="1" <?= $filters['mentor']?'checked':'' ?>>
              <label for="mentor">Mentor only</label>
            </div>
          </div>
        </div>
        <button class="filter-btn" type="submit">
          <i class="fas fa-search"></i>
          Search Wagers
        </button>
      </form>
    </div>
    
    <div class="results-count">
      Found <?= count($wagers) ?> wager<?= count($wagers) !== 1 ? 's' : '' ?>
    </div>
    
    <?php if (empty($wagers)): ?>
      <div class="no-results">
        <i class="fas fa-search"></i>
        <p>No wagers found matching your criteria.</p>
        <p>Try adjusting your search filters.</p>
      </div>
    <?php else: ?>
      <div class="cards">
        <?php foreach ($wagers as $w): ?>
          <div class="card">
            <img src="https://randomuser.me/api/portraits/<?= $w['gender'] === 'Female' ? 'women' : 'men' ?>/<?= $w['id'] ?>.jpg" class="avatar" alt="<?= htmlspecialchars($w['name']) ?>">
            <div class="name">
              <?= htmlspecialchars($w['name']) ?>
              <?php if ($w['is_mentor']): ?><span class="badge">Mentor</span><?php endif; ?>
            </div>
            <div class="role"><?= htmlspecialchars($w['profession']) ?></div>
            <div class="stars"><?= render_star_rating(4.8) ?></div>
            <div class="location">
              <i class="fas fa-map-marker-alt"></i>
              <?= htmlspecialchars($w['location']) ?>
            </div>
            <div class="card-actions">
              <a href="profile.php?id=<?= $w['id'] ?>" class="btn">
                <i class="fas fa-user"></i>
                View Profile
              </a>
              <?php if ($can_review): ?>
                <a href="review.php?wager_id=<?= $w['id'] ?>" class="btn secondary">
                  <i class="fas fa-star"></i>
                  Review
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>