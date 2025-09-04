<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
if ($user['role'] !== 'seeker') { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/components.php';
require_once __DIR__ . '/inc/db.php';

$errors = [];
$success = null;

// Handle new job posting submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_job'])) {
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $budget_min = !empty($_POST['budget_min']) ? (float)$_POST['budget_min'] : null;
  $budget_max = !empty($_POST['budget_max']) ? (float)$_POST['budget_max'] : null;
  $location = trim($_POST['location'] ?? '');
  $job_type = $_POST['job_type'] ?? 'contract';
  $requirements = trim($_POST['requirements'] ?? '');
  $skills_required = trim($_POST['skills_required'] ?? '');
  $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

  if (!$title) { $errors[] = 'Title is required.'; }
  if (!$description) { $errors[] = 'Description is required.'; }

  $image_path = null;
  $file_path = null;

  if (!empty($_FILES['attachment']['name'])) {
    $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
    if ($_FILES['attachment']['size'] > 10*1024*1024) {
      $errors[] = 'File too large (max 10MB).';
    }
    $dangerous = ['exe','bat','cmd','com','pif','scr','vbs','js','jar','msi','dmg','app'];
    if (in_array($ext, $dangerous, true)) {
      $errors[] = 'Executable files are not allowed.';
    }
    if (!$errors) {
      $dest = 'uploads/' . uniqid('job_', true) . ($ext ? ('.' . $ext) : '');
      if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
        $image_exts = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $image_exts, true)) { $image_path = $dest; } else { $file_path = $dest; }
      } else {
        $errors[] = 'Failed to save uploaded file.';
      }
    }
  }

  if (!$errors) {
    $stmt = $pdo->prepare('INSERT INTO job_postings (user_id, title, description, budget_min, budget_max, location, job_type, requirements, skills_required, deadline, image_path, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user['id'], $title, $description, $budget_min, $budget_max, $location ?: null, $job_type, $requirements ?: null, $skills_required ?: null, $deadline, $image_path, $file_path]);
    $success = 'Job posting created successfully.';
  }
}

// Handle regular post submission (like achievements)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  if (!$title) { $errors[] = 'Title is required.'; }

  $image_path = null;
  $file_path = null;

  if (!empty($_FILES['attachment']['name'])) {
    $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
    if ($_FILES['attachment']['size'] > 10*1024*1024) {
      $errors[] = 'File too large (max 10MB).';
    }
    $dangerous = ['exe','bat','cmd','com','pif','scr','vbs','js','jar','msi','dmg','app'];
    if (in_array($ext, $dangerous, true)) {
      $errors[] = 'Executable files are not allowed.';
    }
    if (!$errors) {
      $dest = 'uploads/' . uniqid('post_', true) . ($ext ? ('.' . $ext) : '');
      if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
        $image_exts = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $image_exts, true)) { $image_path = $dest; } else { $file_path = $dest; }
      } else {
        $errors[] = 'Failed to save uploaded file.';
      }
    }
  }

  if (!$errors) {
    $stmt = $pdo->prepare('INSERT INTO portfolio (user_id, title, description, image_path, file_path) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$user['id'], $title, $description ?: null, $image_path, $file_path]);
    $success = 'Post shared successfully.';
  }
}

// Handle delete job posting
if (isset($_POST['delete_job'])) {
  $delId = (int)$_POST['delete_id'];
  $pdo->prepare('DELETE FROM job_postings WHERE id = ? AND user_id = ?')->execute([$delId, $user['id']]);
  $success = 'Job posting deleted.';
}

// Handle delete post
if (isset($_POST['delete_post'])) {
  $delId = (int)$_POST['delete_id'];
  $pdo->prepare('DELETE FROM portfolio WHERE id = ? AND user_id = ?')->execute([$delId, $user['id']]);
  $success = 'Post deleted.';
}

// Fetch job postings
$jobs = $pdo->prepare('SELECT * FROM job_postings WHERE user_id = ? ORDER BY created_at DESC');
$jobs->execute([$user['id']]);
$job_items = $jobs->fetchAll();

// Fetch regular posts
$portfolio = $pdo->prepare('SELECT * FROM portfolio WHERE user_id = ? ORDER BY created_at DESC');
$portfolio->execute([$user['id']]);
$post_items = $portfolio->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Service Seeker Dashboard – Sconnect</title>
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
      max-width: 1200px;
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
      position: relative;
    }
    .header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }
    h2 {
      margin: 0;
      font-size: 2.5rem;
      font-weight: 700;
      position: relative;
      z-index: 1;
    }
    .profile {
      display: flex;
      gap: 2rem;
      align-items: center;
      margin-top: 1.5rem;
      position: relative;
      z-index: 1;
      justify-content: center;
    }
    .profile-info { text-align: left; }
    .profile-info div {
      margin-bottom: 0.5rem;
      font-size: 1.1rem;
    }
    .content {
      padding: 2rem;
    }
    .actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 1.5rem;
      flex-wrap: wrap;
    }
    .btn {
      background: linear-gradient(135deg, #059669 0%, #10b981 100%);
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 0.8rem 2rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
    }
    .btn.secondary {
      background: rgba(5, 150, 105, 0.1);
      color: #059669;
      box-shadow: 0 4px 15px rgba(5, 150, 105, 0.1);
    }
    .btn.secondary:hover {
      background: rgba(5, 150, 105, 0.2);
    }
    .btn.job-btn {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }
    .btn.job-btn:hover {
      box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }
    .post-btn {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 1rem 2rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.1rem;
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
      transition: all 0.3s ease;
    }
    .post-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4);
    }
    .tabs {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      border-bottom: 2px solid #f1f5f9;
    }
    .tab {
      padding: 1rem 2rem;
      background: none;
      border: none;
      font-size: 1.1rem;
      font-weight: 600;
      color: #64748b;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      transition: all 0.3s ease;
    }
    .tab.active {
      color: #059669;
      border-bottom-color: #059669;
    }
    .tab:hover {
      color: #059669;
    }
    .section {
      display: none;
    }
    .section.active {
      display: block;
    }
    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid #f1f5f9;
    }
    .section-header h3 {
      margin: 0;
      font-size: 1.8rem;
      color: #1e293b;
      font-weight: 700;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
      font-size: 1.2rem;
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
    .delete-btn {
      background: #ef4444;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .delete-btn:hover {
      background: #dc2626;
      transform: scale(1.05);
    }
    .muted { color: #64748b; font-size: 0.9rem; }
    .error {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #991b1b;
      padding: 1rem 1.5rem;
      border-radius: 12px;
      margin: 1rem 0;
      border-left: 4px solid #ef4444;
    }
    .success {
      background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
      color: #14532d;
      padding: 1rem 1.5rem;
      border-radius: 12px;
      margin: 1rem 0;
      border-left: 4px solid #10b981;
    }

    /* Instagram-inspired empty state */
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
      .header { padding: 1.5rem; }
      h2 { font-size: 2rem; }
      .profile { flex-direction: column; text-align: center; }
      .content { padding: 1.5rem; }
      .grid { grid-template-columns: 1fr; }
      .actions { flex-direction: column; align-items: center; }
      .section-header { flex-direction: column; gap: 1rem; align-items: stretch; }
      .tabs { flex-direction: column; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
      <div class="profile">
        <div class="profile-info">
          <div><b>Email:</b> <?= htmlspecialchars($user['email']) ?></div>
          <div><b>Phone:</b> <?= htmlspecialchars($user['phone']) ?></div>
          <div><b>Role:</b> Service Seeker</div>
        </div>
      </div>
      <div class="actions">
        <a href="search.php" class="btn">
          <i class="fas fa-search"></i> Search Wagers
        </a>
        <a href="chat.php" class="btn secondary">
          <i class="fas fa-comments"></i> Chatroom
        </a>
        <a href="logout.php" class="btn secondary">
          <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
      </div>
    </div>

    <div class="content">
      <?php if ($errors): ?>
        <div class="error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <div class="tabs">
        <button class="tab active" onclick="showSection('jobs')">
          <i class="fas fa-briefcase"></i> Job Postings
        </button>
        <button class="tab" onclick="showSection('posts')">
          <i class="fas fa-camera"></i> Posts
        </button>
      </div>

      <!-- Job Postings Section -->
      <div id="jobs-section" class="section active">
        <div class="section-header">
          <h3>Your Job Postings</h3>
          <button class="post-btn job-btn" id="show-job-form" type="button">
            <i class="fas fa-plus"></i> Post Job
          </button>
        </div>

        <form class="form" method="post" enctype="multipart/form-data" id="job-form" style="display:none; margin-bottom:2rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; border-radius: 16px; padding: 2rem;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="group">
              <label for="job-title" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Job Title</b></label>
              <input type="text" id="job-title" name="title" placeholder="e.g., Need a Website Developer" required style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: border-color 0.3s ease;">
            </div>
            <div class="group">
              <label for="job-type" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Job Type</b></label>
              <select id="job-type" name="job_type" style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: border-color 0.3s ease;">
                <option value="contract">Contract</option>
                <option value="freelance">Freelance</option>
                <option value="part-time">Part-time</option>
                <option value="full-time">Full-time</option>
              </select>
            </div>
          </div>
          <div class="group">
            <label for="job-description" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Description</b></label>
            <textarea id="job-description" name="description" placeholder="Describe the job requirements, expectations, and deliverables..." required style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; min-height: 120px; resize: vertical; transition: border-color 0.3s ease;"></textarea>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="group">
              <label for="budget-min" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Min Budget ($)</b></label>
              <input type="number" id="budget-min" name="budget_min" placeholder="500" min="0" step="0.01" style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: border-color 0.3s ease;">
            </div>
            <div class="group">
              <label for="budget-max" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Max Budget ($)</b></label>
              <input type="number" id="budget-max" name="budget_max" placeholder="2000" min="0" step="0.01" style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: border-color 0.3s ease;">
            </div>
            <div class="group">
              <label for="deadline" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Deadline</b></label>
              <input type="date" id="deadline" name="deadline" style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: border-color 0.3s ease;">
            </div>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="group">
              <label for="location" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Location</b></label>
              <input type="text" id="location" name="location" placeholder="Remote, New York, etc." style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: border-color 0.3s ease;">
            </div>
            <div class="group">
              <label for="skills" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Skills Required</b></label>
              <input type="text" id="skills" name="skills_required" placeholder="PHP, JavaScript, React..." style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: border-color 0.3s ease;">
            </div>
          </div>
          <div class="group">
            <label for="requirements" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Requirements (optional)</b></label>
            <textarea id="requirements" name="requirements" placeholder="Additional requirements, qualifications, or preferences..." style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; min-height: 80px; resize: vertical; transition: border-color 0.3s ease;"></textarea>
          </div>
          <div class="group">
            <label for="job-attachment" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Attach files (optional)</b></label>
            <input type="file" id="job-attachment" name="attachment" style="padding: 0.8rem; border: 2px dashed #d1d5db; border-radius: 12px; background: #f9fafb;">
            <div class="muted" style="margin-top: 0.5rem;">Attach project briefs, mockups, or reference materials (max 10MB).</div>
          </div>
          <div class="inline-actions" style="gap: 1rem; display: flex;">
            <button class="btn job-btn" type="submit" name="add_job" style="flex: 1;">
              <i class="fas fa-briefcase"></i> Post Job
            </button>
            <button class="btn secondary" type="button" id="cancel-job-form" style="flex: 1;">
              <i class="fas fa-times"></i> Cancel
            </button>
          </div>
        </form>

        <div class="grid">
          <?php if (!$job_items): ?>
            <div class="empty-state">
              <i class="fas fa-briefcase"></i>
              <h4>Post Your First Job</h4>
              <p>Start finding the perfect wager for your project by posting your first job opportunity.</p>
              <button class="post-btn job-btn" onclick="document.getElementById('show-job-form').click()">
                <i class="fas fa-plus"></i> Post Your First Job
              </button>
            </div>
          <?php else: ?>
            <?php foreach ($job_items as $job): ?>
              <div class="card">
                <?php if (!empty($job['image_path'])): ?>
                  <img src="<?= htmlspecialchars($job['image_path']) ?>" alt="Job" loading="lazy">
                <?php endif; ?>
                <div class="card-content">
                  <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                    <div class="card-title"><?= htmlspecialchars($job['title']) ?></div>
                    <span class="job-badge"><?= htmlspecialchars($job['job_type']) ?></span>
                  </div>
                  <div class="card-description"><?= nl2br(htmlspecialchars($job['description'])) ?></div>

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

                  <?php if ($job['location']): ?>
                    <div class="muted" style="margin-top: 0.5rem;">
                      <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location']) ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($job['deadline']): ?>
                    <div class="muted" style="margin-top: 0.5rem;">
                      <i class="fas fa-calendar"></i> Deadline: <?= date('M j, Y', strtotime($job['deadline'])) ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($job['skills_required']): ?>
                    <div class="muted" style="margin-top: 0.5rem;">
                      <i class="fas fa-tags"></i> <?= htmlspecialchars($job['skills_required']) ?>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($job['file_path'])): ?>
                    <div style="margin-top: 1rem;">
                      <a href="<?= htmlspecialchars($job['file_path']) ?>" target="_blank" class="btn secondary" style="padding:0.6rem 1.2rem; font-size: 0.9rem;">
                        <i class="fas fa-download"></i> Download Attachment
                      </a>
                    </div>
                  <?php endif; ?>

                  <div class="card-meta">
                    <span class="muted">
                      <i class="fas fa-clock"></i>
                      <?= date('M j, Y', strtotime($job['created_at'])) ?>
                    </span>
                    <form method="post" style="margin: 0;">
                      <input type="hidden" name="delete_id" value="<?= (int)$job['id'] ?>">
                      <button type="submit" name="delete_job" class="delete-btn" onclick="return confirm('Are you sure you want to delete this job posting?')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Posts Section -->
      <div id="posts-section" class="section">
        <div class="section-header">
          <h3>Your Posts</h3>
          <button class="post-btn" id="show-post-form" type="button">
            <i class="fas fa-plus"></i> Create Post
          </button>
        </div>

        <form class="form" method="post" enctype="multipart/form-data" id="post-form" style="display:none; margin-bottom:2rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; border-radius: 16px; padding: 2rem;">
          <div class="group">
            <label for="post-title" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Title</b></label>
            <input type="text" id="post-title" name="title" placeholder="e.g., Looking for feedback on my project idea" required style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: border-color 0.3s ease;">
          </div>
          <div class="group">
            <label for="post-description" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Description</b></label>
            <textarea id="post-description" name="description" placeholder="Share your thoughts, ideas, or updates..." style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1rem; font-size: 1rem; min-height: 120px; resize: vertical; transition: border-color 0.3s ease;"></textarea>
          </div>
          <div class="group">
            <label for="post-attachment" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><b>Attach media or document (optional)</b></label>
            <input type="file" id="post-attachment" name="attachment" style="padding: 0.8rem; border: 2px dashed #d1d5db; border-radius: 12px; background: #f9fafb;">
            <div class="muted" style="margin-top: 0.5rem;">Accepts any file type (max 10MB). Images will preview; other files will be downloadable.</div>
          </div>
          <div class="inline-actions" style="gap: 1rem; display: flex;">
            <button class="btn" type="submit" name="add_post" style="flex: 1;">
              <i class="fas fa-paper-plane"></i> Share Post
            </button>
            <button class="btn secondary" type="button" id="cancel-post-form" style="flex: 1;">
              <i class="fas fa-times"></i> Cancel
            </button>
          </div>
        </form>

        <div class="grid">
          <?php if (!$post_items): ?>
            <div class="empty-state">
              <i class="fas fa-camera"></i>
              <h4>Share Your First Post</h4>
              <p>Connect with the community by sharing your thoughts, ideas, or updates.</p>
              <button class="post-btn" onclick="document.getElementById('show-post-form').click()">
                <i class="fas fa-plus"></i> Create Your First Post
              </button>
            </div>
          <?php else: ?>
            <?php foreach ($post_items as $post): ?>
              <div class="card">
                <?php if (!empty($post['image_path'])): ?>
                  <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post" loading="lazy">
                <?php endif; ?>
                <div class="card-content">
                  <div class="card-title"><?= htmlspecialchars($post['title']) ?></div>
                  <?php if (!empty($post['description'])): ?>
                    <div class="card-description"><?= nl2br(htmlspecialchars($post['description'])) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($post['file_path'])): ?>
                    <div style="margin-top: 1rem;">
                      <a href="<?= htmlspecialchars($post['file_path']) ?>" target="_blank" class="btn secondary" style="padding:0.6rem 1.2rem; font-size: 0.9rem;">
                        <i class="fas fa-download"></i> Download
                      </a>
                    </div>
                  <?php endif; ?>
                  <div class="card-meta">
                    <span class="muted">
                      <i class="fas fa-clock"></i>
                      <?= date('M j, Y', strtotime($post['created_at'])) ?>
                    </span>
                    <form method="post" style="margin: 0;">
                      <input type="hidden" name="delete_id" value="<?= (int)$post['id'] ?>">
                      <button type="submit" name="delete_post" class="delete-btn" onclick="return confirm('Are you sure you want to delete this post?')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <script>
      // Tab switching functionality
      function showSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => {
          section.classList.remove('active');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.tab').forEach(tab => {
          tab.classList.remove('active');
        });

        // Show selected section
        document.getElementById(sectionName + '-section').classList.add('active');

        // Add active class to clicked tab
        event.target.classList.add('active');
      }

      // Toggle job form
      const showJobBtn = document.getElementById('show-job-form');
      const jobForm = document.getElementById('job-form');
      const cancelJobBtn = document.getElementById('cancel-job-form');

      showJobBtn.addEventListener('click', () => {
        jobForm.style.display = 'block';
        jobForm.style.opacity = '0';
        jobForm.style.transform = 'translateY(-20px)';
        setTimeout(() => {
          jobForm.style.transition = 'all 0.3s ease';
          jobForm.style.opacity = '1';
          jobForm.style.transform = 'translateY(0)';
        }, 10);
        showJobBtn.style.display = 'none';
      });

      cancelJobBtn.addEventListener('click', () => {
        jobForm.style.transition = 'all 0.3s ease';
        jobForm.style.opacity = '0';
        jobForm.style.transform = 'translateY(-20px)';
        setTimeout(() => {
          jobForm.style.display = 'none';
          showJobBtn.style.display = 'flex';
        }, 300);
      });

      // Toggle post form
      const showPostBtn = document.getElementById('show-post-form');
      const postForm = document.getElementById('post-form');
      const cancelPostBtn = document.getElementById('cancel-post-form');

      showPostBtn.addEventListener('click', () => {
        postForm.style.display = 'block';
        postForm.style.opacity = '0';
        postForm.style.transform = 'translateY(-20px)';
        setTimeout(() => {
          postForm.style.transition = 'all 0.3s ease';
          postForm.style.opacity = '1';
          postForm.style.transform = 'translateY(0)';
        }, 10);
        showPostBtn.style.display = 'none';
      });

      cancelPostBtn.addEventListener('click', () => {
        postForm.style.transition = 'all 0.3s ease';
        postForm.style.opacity = '0';
        postForm.style.transform = 'translateY(-20px)';
        setTimeout(() => {
          postForm.style.display = 'none';
          showPostBtn.style.display = 'flex';
        }, 300);
      });

      // Add focus effects to form inputs
      const inputs = document.querySelectorAll('input, textarea, select');
      inputs.forEach(input => {
        input.addEventListener('focus', () => {
          input.style.borderColor = '#059669';
          input.style.boxShadow = '0 0 0 3px rgba(5, 150, 105, 0.1)';
        });
        input.addEventListener('blur', () => {
          input.style.borderColor = '#e5e7eb';
          input.style.boxShadow = 'none';
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
  </div>
</body>
</html>