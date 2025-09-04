<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
if ($user['role'] !== 'job_provider') { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/components.php';
require_once __DIR__ . '/inc/db.php';

// Get search parameters
$search_query = trim($_GET['q'] ?? '');
$category_filter = $_GET['category'] ?? '';
$experience_filter = $_GET['experience'] ?? '';
$availability_filter = $_GET['availability'] ?? '';
$location_filter = trim($_GET['location'] ?? '');
$min_rate = !empty($_GET['min_rate']) ? (float)$_GET['min_rate'] : null;
$max_rate = !empty($_GET['max_rate']) ? (float)$_GET['max_rate'] : null;

// Fetch job categories for filter
$job_categories = $pdo->query("SELECT * FROM job_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

// Build search query
$where_conditions = ["u.role = 'job_seeker'"];
$params = [];

if ($search_query) {
    $where_conditions[] = "(u.name LIKE ? OR u.bio LIKE ? OR s.name LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($category_filter) {
    $where_conditions[] = "jc.id = ?";
    $params[] = $category_filter;
}

if ($experience_filter) {
    $where_conditions[] = "u.experience_level = ?";
    $params[] = $experience_filter;
}

if ($availability_filter) {
    $where_conditions[] = "u.availability = ?";
    $params[] = $availability_filter;
}

if ($location_filter) {
    $where_conditions[] = "u.location LIKE ?";
    $params[] = "%$location_filter%";
}

if ($min_rate !== null) {
    $where_conditions[] = "u.hourly_rate >= ?";
    $params[] = $min_rate;
}

if ($max_rate !== null) {
    $where_conditions[] = "u.hourly_rate <= ?";
    $params[] = $max_rate;
}

$where_clause = implode(' AND ', $where_conditions);

// Execute search query
$sql = "
    SELECT u.*, 
           GROUP_CONCAT(DISTINCT jc.name) as categories, 
           GROUP_CONCAT(DISTINCT s.name) as skills,
           COUNT(DISTINCT p.id) as portfolio_count
    FROM users u 
    LEFT JOIN user_job_categories ujc ON u.id = ujc.user_id 
    LEFT JOIN job_categories jc ON ujc.category_id = jc.id
    LEFT JOIN user_skills us ON u.id = us.user_id
    LEFT JOIN skills s ON us.skill_id = s.id
    LEFT JOIN portfolio p ON u.id = p.user_id
    WHERE $where_clause
    GROUP BY u.id
    ORDER BY u.rating DESC, u.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$seekers = $stmt->fetchAll();

$total_results = count($seekers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Talent – Sconnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body { 
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      margin: 0; 
      min-height: 100vh;
      padding: 1rem;
    }
    .container { 
      max-width: 1400px; 
      margin: 0 auto; 
      background: #fff; 
      border-radius: 20px; 
      box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
      overflow: hidden;
    }
    .header {
      background: linear-gradient(135deg, #059669 0%, #10b981 100%);
      color: white;
      padding: 2rem;
      text-align: center;
    }
    h1 { 
      margin: 0; 
      font-size: 2.5rem; 
      font-weight: 700;
    }
    .search-section {
      background: #f8fafc;
      padding: 2rem;
      border-bottom: 1px solid #e2e8f0;
    }
    .search-form {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr 1fr auto;
      gap: 1rem;
      align-items: end;
    }
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    .form-group label {
      font-weight: 600;
      color: #374151;
      font-size: 0.9rem;
    }
    .form-group input, .form-group select {
      padding: 0.8rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }
    .form-group input:focus, .form-group select:focus {
      outline: none;
      border-color: #059669;
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
    }
    .search-btn {
      background: linear-gradient(135deg, #059669 0%, #10b981 100%);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 0.8rem 1.5rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      height: fit-content;
    }
    .search-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }
    .filters-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    .content {
      padding: 2rem;
    }
    .results-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid #f1f5f9;
    }
    .results-count {
      font-size: 1.2rem;
      color: #374151;
      font-weight: 600;
    }
    .back-btn {
      background: rgba(5, 150, 105, 0.1);
      color: #059669;
      border: none;
      border-radius: 50px;
      padding: 0.8rem 1.5rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
    }
    .back-btn:hover {
      background: rgba(5, 150, 105, 0.2);
    }
    .grid { 
      display: grid; 
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); 
      gap: 1.5rem; 
    }
    .card { 
      background: #fff;
      border-radius: 16px; 
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      position: relative;
      border: 1px solid #e2e8f0;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    .card-content {
      padding: 1.5rem;
    }
    .card-title {
      font-weight: 700;
      font-size: 1.3rem;
      color: #1e293b;
      margin-bottom: 0.5rem;
      line-height: 1.4;
    }
    .card-description {
      color: #64748b;
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 1rem;
    }
    .card-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid #f1f5f9;
    }
    .budget {
      color: #059669;
      font-weight: 600;
      font-size: 1.1rem;
    }
    .btn { 
      background: linear-gradient(135deg, #059669 0%, #10b981 100%);
      color: #fff; 
      border: none; 
      border-radius: 8px; 
      padding: 0.6rem 1.2rem; 
      font-weight: 600; 
      cursor: pointer; 
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }
    .btn.secondary { 
      background: rgba(5, 150, 105, 0.1);
      color: #059669; 
    }
    .btn.secondary:hover {
      background: rgba(5, 150, 105, 0.2);
    }
    .muted { color: #64748b; font-size: 0.9rem; }
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #64748b;
      grid-column: 1 / -1;
    }
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 1rem;
      color: #cbd5e1;
    }
    .empty-state h4 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
      color: #475569;
    }
    .empty-state p {
      font-size: 1.1rem;
      margin-bottom: 2rem;
    }
    
    @media (max-width: 768px) { 
      .container { margin: 0; border-radius: 0; }
      .search-form { grid-template-columns: 1fr; }
      .filters-row { grid-template-columns: 1fr; }
      .grid { grid-template-columns: 1fr; }
      .results-header { flex-direction: column; gap: 1rem; align-items: stretch; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1><i class="fas fa-search"></i> Search Talent</h1>
      <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Find the perfect talent for your projects</p>
    </div>

    <div class="search-section">
      <form method="get" class="search-form">
        <div class="form-group">
          <label for="q">Search</label>
          <input type="text" id="q" name="q" placeholder="Search by name, skills, or bio..." value="<?= htmlspecialchars($search_query) ?>">
        </div>

        <div class="form-group">
          <label for="category">Category</label>
          <select id="category" name="category">
            <option value="">All Categories</option>
            <?php foreach ($job_categories as $category): ?>
              <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="experience">Experience</label>
          <select id="experience" name="experience">
            <option value="">Any Experience</option>
            <option value="entry" <?= $experience_filter === 'entry' ? 'selected' : '' ?>>Entry Level</option>
            <option value="intermediate" <?= $experience_filter === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
            <option value="senior" <?= $experience_filter === 'senior' ? 'selected' : '' ?>>Senior</option>
            <option value="expert" <?= $experience_filter === 'expert' ? 'selected' : '' ?>>Expert</option>
          </select>
        </div>

        <div class="form-group">
          <label for="availability">Availability</label>
          <select id="availability" name="availability">
            <option value="">Any Availability</option>
            <option value="full-time" <?= $availability_filter === 'full-time' ? 'selected' : '' ?>>Full-time</option>
            <option value="part-time" <?= $availability_filter === 'part-time' ? 'selected' : '' ?>>Part-time</option>
            <option value="freelance" <?= $availability_filter === 'freelance' ? 'selected' : '' ?>>Freelance</option>
            <option value="contract" <?= $availability_filter === 'contract' ? 'selected' : '' ?>>Contract</option>
          </select>
        </div>

        <button type="submit" class="search-btn">
          <i class="fas fa-search"></i> Search
        </button>
      </form>

      <div class="filters-row">
        <div class="form-group">
          <label for="location">Location</label>
          <input type="text" id="location" name="location" placeholder="City, Country..." value="<?= htmlspecialchars($location_filter) ?>" form="search-form">
        </div>

        <div class="form-group">
          <label for="min_rate">Min Rate ($/hour)</label>
          <input type="number" id="min_rate" name="min_rate" placeholder="0" min="0" step="0.01" value="<?= $min_rate ?>" form="search-form">
        </div>

        <div class="form-group">
          <label for="max_rate">Max Rate ($/hour)</label>
          <input type="number" id="max_rate" name="max_rate" placeholder="1000" min="0" step="0.01" value="<?= $max_rate ?>" form="search-form">
        </div>
      </div>
    </div>

    <div class="content">
      <div class="results-header">
        <div class="results-count">
          <?= $total_results ?> talent<?= $total_results !== 1 ? 's' : '' ?> found
        </div>
        <a href="dashboard_job_provider.php" class="back-btn">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      </div>

      <div class="grid">
        <?php if (!$seekers): ?>
          <div class="empty-state">
            <i class="fas fa-search"></i>
            <h4>No Talent Found</h4>
            <p>Try adjusting your search criteria to find more candidates.</p>
            <button onclick="document.getElementById('q').value=''; document.querySelector('form').submit();" class="btn">
              <i class="fas fa-refresh"></i> Clear Filters
            </button>
          </div>
        <?php else: ?>
          <?php foreach ($seekers as $seeker): ?>
            <div class="card">
              <div class="card-content">
                <div class="card-title"><?= htmlspecialchars($seeker['name']) ?></div>

                <div style="margin: 1rem 0;">
                  <?php if ($seeker['experience_level']): ?>
                    <div class="muted">
                      <i class="fas fa-chart-line"></i> <?= ucfirst($seeker['experience_level']) ?> Level
                    </div>
                  <?php endif; ?>

                  <?php if ($seeker['availability']): ?>
                    <div class="muted">
                      <i class="fas fa-clock"></i> Available for <?= ucfirst($seeker['availability']) ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($seeker['location']): ?>
                    <div class="muted">
                      <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($seeker['location']) ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($seeker['hourly_rate']): ?>
                    <div class="budget">
                      <i class="fas fa-dollar-sign"></i> $<?= number_format($seeker['hourly_rate'], 2) ?>/hour
                    </div>
                  <?php endif; ?>

                  <div class="muted">
                    <i class="fas fa-briefcase"></i> <?= $seeker['portfolio_count'] ?> portfolio item<?= $seeker['portfolio_count'] !== 1 ? 's' : '' ?>
                  </div>
                </div>

                <?php if ($seeker['categories']): ?>
                  <div style="margin: 1rem 0;">
                    <div class="muted" style="font-weight: 600; margin-bottom: 0.5rem;">Categories:</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                      <?php foreach (explode(',', $seeker['categories']) as $category): ?>
                        <?php if (trim($category)): ?>
                          <span style="background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.8rem;">
                            <?= htmlspecialchars(trim($category)) ?>
                          </span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if ($seeker['skills']): ?>
                  <div style="margin: 1rem 0;">
                    <div class="muted" style="font-weight: 600; margin-bottom: 0.5rem;">Skills:</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                      <?php
                      $skills_array = array_slice(explode(',', $seeker['skills']), 0, 8); // Show max 8 skills
                      foreach ($skills_array as $skill): ?>
                        <?php if (trim($skill)): ?>
                          <span style="background: #f0fdf4; color: #15803d; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.8rem;">
                            <?= htmlspecialchars(trim($skill)) ?>
                          </span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                      <?php if (count(explode(',', $seeker['skills'])) > 8): ?>
                        <span style="color: #64748b; font-size: 0.8rem;">+<?= count(explode(',', $seeker['skills'])) - 8 ?> more</span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if ($seeker['bio']): ?>
                  <div class="card-description">
                    <?= nl2br(htmlspecialchars(substr($seeker['bio'], 0, 200))) ?><?= strlen($seeker['bio']) > 200 ? '...' : '' ?>
                  </div>
                <?php endif; ?>

                <div class="card-meta">
                  <span class="muted">
                    <i class="fas fa-star"></i>
                    Rating: <?= number_format($seeker['rating'], 1) ?>/5.0
                  </span>
                  <div style="display: flex; gap: 0.5rem;">
                    <a href="profile.php?id=<?= $seeker['id'] ?>" class="btn secondary">
                      <i class="fas fa-user"></i> View Profile
                    </a>
                    <a href="chat.php?to=<?= $seeker['id'] ?>" class="btn">
                      <i class="fas fa-comment"></i> Contact
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    // Add form id for filter inputs
    document.getElementById('search-form') || (function() {
      const form = document.querySelector('form');
      form.id = 'search-form';
    })();

    // Auto-submit form when filters change
    const filterInputs = document.querySelectorAll('#location, #min_rate, #max_rate');
    filterInputs.forEach(input => {
      input.addEventListener('change', () => {
        document.getElementById('search-form').submit();
      });
    });

    // Add hover effects to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
      card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-5px)';
      });
      card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
      });
    });
  </script>
</body>
</html>
