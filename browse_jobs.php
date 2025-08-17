<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
if ($user['role'] !== 'job_seeker') { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/components.php';
require_once __DIR__ . '/inc/db.php';

// Get search parameters
$search_query = trim($_GET['q'] ?? '');
$category_filter = $_GET['category'] ?? '';
$job_type_filter = $_GET['job_type'] ?? '';
$location_filter = trim($_GET['location'] ?? '');
$min_budget = !empty($_GET['min_budget']) ? (float)$_GET['min_budget'] : null;
$max_budget = !empty($_GET['max_budget']) ? (float)$_GET['max_budget'] : null;

// Fetch job categories for filter
$job_categories = $pdo->query("SELECT * FROM job_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

// Build search query
$where_conditions = ["jp.status = 'open'"];
$params = [];

if ($search_query) {
    $where_conditions[] = "(jp.title LIKE ? OR jp.description LIKE ? OR jp.skills_required LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($job_type_filter) {
    $where_conditions[] = "jp.job_type = ?";
    $params[] = $job_type_filter;
}

if ($location_filter) {
    $where_conditions[] = "(jp.location LIKE ? OR u.location LIKE ?)";
    $location_term = "%$location_filter%";
    $params[] = $location_term;
    $params[] = $location_term;
}

if ($min_budget !== null) {
    $where_conditions[] = "(jp.budget_min >= ? OR jp.budget_max >= ?)";
    $params[] = $min_budget;
    $params[] = $min_budget;
}

if ($max_budget !== null) {
    $where_conditions[] = "(jp.budget_max <= ? OR jp.budget_min <= ?)";
    $params[] = $max_budget;
    $params[] = $max_budget;
}

$where_clause = implode(' AND ', $where_conditions);

// Execute search query
$sql = "
    SELECT jp.*, u.name as company_name, u.location as company_location, u.profession as company_type
    FROM job_postings jp 
    JOIN users u ON jp.user_id = u.id 
    WHERE $where_clause
    ORDER BY jp.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$total_results = count($jobs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Browse Jobs – Sconnect</title>
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
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .search-btn {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
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
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
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
      background: rgba(59, 130, 246, 0.2);
    }
    .grid { 
      display: grid; 
      grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); 
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
    .card img { 
      width: 100%; 
      height: 200px; 
      object-fit: cover; 
      border-bottom: 1px solid #e2e8f0;
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
    .job-badge {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: white;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
    }
    .budget {
      color: #059669;
      font-weight: 600;
      font-size: 1.1rem;
    }
    .btn { 
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    .btn.secondary { 
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6; 
    }
    .btn.secondary:hover {
      background: rgba(59, 130, 246, 0.2);
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
      <h1><i class="fas fa-briefcase"></i> Browse Jobs</h1>
      <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Find your next opportunity</p>
    </div>

    <div class="search-section">
      <form method="get" class="search-form">
        <div class="form-group">
          <label for="q">Search</label>
          <input type="text" id="q" name="q" placeholder="Search by title, description, or skills..." value="<?= htmlspecialchars($search_query) ?>">
        </div>

        <div class="form-group">
          <label for="job_type">Job Type</label>
          <select id="job_type" name="job_type">
            <option value="">All Types</option>
            <option value="full-time" <?= $job_type_filter === 'full-time' ? 'selected' : '' ?>>Full-time</option>
            <option value="part-time" <?= $job_type_filter === 'part-time' ? 'selected' : '' ?>>Part-time</option>
            <option value="freelance" <?= $job_type_filter === 'freelance' ? 'selected' : '' ?>>Freelance</option>
            <option value="contract" <?= $job_type_filter === 'contract' ? 'selected' : '' ?>>Contract</option>
          </select>
        </div>

        <div class="form-group">
          <label for="location">Location</label>
          <input type="text" id="location" name="location" placeholder="City, Country..." value="<?= htmlspecialchars($location_filter) ?>">
        </div>

        <div class="form-group">
          <label for="min_budget">Min Budget ($)</label>
          <input type="number" id="min_budget" name="min_budget" placeholder="0" min="0" step="0.01" value="<?= $min_budget ?>">
        </div>

        <button type="submit" class="search-btn">
          <i class="fas fa-search"></i> Search
        </button>
      </form>

      <div class="filters-row">
        <div class="form-group">
          <label for="max_budget">Max Budget ($)</label>
          <input type="number" id="max_budget" name="max_budget" placeholder="10000" min="0" step="0.01" value="<?= $max_budget ?>" form="search-form">
        </div>
      </div>
    </div>

    <div class="content">
      <div class="results-header">
        <div class="results-count">
          <?= $total_results ?> job<?= $total_results !== 1 ? 's' : '' ?> found
        </div>
        <a href="dashboard_job_seeker.php" class="back-btn">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      </div>

      <div class="grid">
        <?php if (!$jobs): ?>
          <div class="empty-state">
            <i class="fas fa-search"></i>
            <h4>No Jobs Found</h4>
            <p>Try adjusting your search criteria to find more opportunities.</p>
            <button onclick="document.getElementById('q').value=''; document.querySelector('form').submit();" class="btn">
              <i class="fas fa-refresh"></i> Clear Filters
            </button>
          </div>
        <?php else: ?>
          <?php foreach ($jobs as $job): ?>
            <div class="card">
              <?php if (!empty($job['image_path'])): ?>
                <img src="<?= htmlspecialchars($job['image_path']) ?>" alt="Job" loading="lazy">
              <?php endif; ?>
              <div class="card-content">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                  <div class="card-title"><?= htmlspecialchars($job['title']) ?></div>
                  <span class="job-badge"><?= htmlspecialchars($job['job_type']) ?></span>
                </div>

                <div style="margin: 1rem 0;">
                  <div class="muted">
                    <i class="fas fa-building"></i> <?= htmlspecialchars($job['company_name']) ?>
                    <?php if ($job['company_type']): ?>
                      (<?= htmlspecialchars($job['company_type']) ?>)
                    <?php endif; ?>
                  </div>
                  <?php if ($job['company_location'] || $job['location']): ?>
                    <div class="muted">
                      <i class="fas fa-map-marker-alt"></i>
                      <?= htmlspecialchars($job['location'] ?: $job['company_location']) ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="card-description">
                  <?= nl2br(htmlspecialchars(substr($job['description'], 0, 200))) ?><?= strlen($job['description']) > 200 ? '...' : '' ?>
                </div>

                <?php if ($job['budget_min'] || $job['budget_max']): ?>
                  <div class="budget">
                    <i class="fas fa-dollar-sign"></i>
                    <?php if ($job['budget_min'] && $job['budget_max']): ?>
                      $<?= number_format($job['budget_min']) ?> - $<?= number_format($job['budget_max']) ?>
                    <?php elseif ($job['budget_min']): ?>
                      From $<?= number_format($job['budget_min']) ?>
                    <?php else: ?>
                      Up to $<?= number_format($job['budget_max']) ?>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <?php if ($job['deadline']): ?>
                  <div class="muted" style="margin-top: 0.5rem;">
                    <i class="fas fa-calendar"></i> Deadline: <?= date('M j, Y', strtotime($job['deadline'])) ?>
                  </div>
                <?php endif; ?>

                <?php if ($job['skills_required']): ?>
                  <div style="margin: 1rem 0;">
                    <div class="muted" style="font-weight: 600; margin-bottom: 0.5rem;">Skills Required:</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                      <?php
                      $skills_array = array_slice(explode(',', $job['skills_required']), 0, 6);
                      foreach ($skills_array as $skill): ?>
                        <?php if (trim($skill)): ?>
                          <span style="background: #f0fdf4; color: #15803d; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.8rem;">
                            <?= htmlspecialchars(trim($skill)) ?>
                          </span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                      <?php if (count(explode(',', $job['skills_required'])) > 6): ?>
                        <span style="color: #64748b; font-size: 0.8rem;">+<?= count(explode(',', $job['skills_required'])) - 6 ?> more</span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (!empty($job['file_path'])): ?>
                  <div style="margin-top: 1rem;">
                    <a href="<?= htmlspecialchars($job['file_path']) ?>" target="_blank" class="btn secondary" style="padding:0.6rem 1.2rem; font-size: 0.9rem;">
                      <i class="fas fa-download"></i> Download Brief
                    </a>
                  </div>
                <?php endif; ?>

                <div class="card-meta">
                  <span class="muted">
                    <i class="fas fa-clock"></i>
                    Posted <?= date('M j, Y', strtotime($job['created_at'])) ?>
                  </span>
                  <div style="display: flex; gap: 0.5rem;">
                    <a href="job_details.php?id=<?= $job['id'] ?>" class="btn secondary">
                      <i class="fas fa-eye"></i> View Details
                    </a>
                    <a href="chat.php?to=<?= $job['user_id'] ?>" class="btn">
                      <i class="fas fa-comment"></i> Apply
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
    const filterInputs = document.querySelectorAll('#max_budget');
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
