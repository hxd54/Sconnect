<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
if ($user['role'] !== 'job_provider') { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/components.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/chatbot.php';
require_once __DIR__ . '/inc/background.php';

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

// Handle delete job posting
if (isset($_POST['delete_job'])) {
  $delId = (int)$_POST['delete_id'];
  $pdo->prepare('DELETE FROM job_postings WHERE id = ? AND user_id = ?')->execute([$delId, $user['id']]);
  $success = 'Job posting deleted.';
}

// Fetch user's job postings with like counts and user's like status
$jobs = $pdo->prepare('
  SELECT jp.*,
         COUNT(pl.id) as like_count,
         MAX(CASE WHEN pl.user_id = ? THEN 1 ELSE 0 END) as user_liked
  FROM job_postings jp
  LEFT JOIN post_likes pl ON jp.id = pl.job_posting_id AND pl.like_type = "job_posting"
  WHERE jp.user_id = ?
  GROUP BY jp.id
  ORDER BY jp.created_at DESC
');
$jobs->execute([$user['id'], $user['id']]);
$job_items = $jobs->fetchAll();

// Fetch recent job seekers
$seekers = [];
try {
  $job_seekers = $pdo->prepare('
    SELECT u.*, GROUP_CONCAT(DISTINCT jc.name) as categories, GROUP_CONCAT(DISTINCT s.name) as skills
    FROM users u
    LEFT JOIN user_job_categories ujc ON u.id = ujc.user_id
    LEFT JOIN job_categories jc ON ujc.category_id = jc.id
    LEFT JOIN user_skills us ON u.id = us.user_id
    LEFT JOIN skills s ON us.skill_id = s.id
    WHERE u.role = "job_seeker"
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT 12
  ');
  $job_seekers->execute();
  $seekers = $job_seekers->fetchAll();
} catch (PDOException $e) {
  if (strpos($e->getMessage(), "doesn't exist") !== false) {
    $errors[] = "Database tables not found. Please run the database setup first.";
  } else {
    $errors[] = "Database error: " . $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Job Provider Dashboard - SConnect</title>
  <link href="https://fonts.googleapis.com/css2?family=Billabong&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      background-attachment: fixed;
      margin: 0;
      min-height: 100vh;
    }

    /* Instagram Header */
    .header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      padding: 1rem;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 975px;
      margin: 0 auto;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: #262626;
      text-decoration: none;
      font-family: 'Billabong', cursive;
    }

    .header-nav {
      display: flex;
      gap: 1.5rem;
      align-items: center;
    }

    .header-btn {
      background: none;
      border: none;
      color: #262626;
      font-size: 1.3rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .header-btn:hover {
      background: #f5f5f5;
    }

    /* Main Content */
    .main-content {
      max-width: 975px;
      margin: 0 auto;
      padding: 2rem 1rem;
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 2rem;
    }

    @media (max-width: 1024px) {
      .main-content {
        grid-template-columns: 1fr;
        padding: 1rem;
      }
    }

    /* Post Creation */
    .create-post {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      transition: all 0.3s ease;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .create-post:hover {
      background: rgba(255, 255, 255, 1);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
    }

    .create-post-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid transparent;
      background: linear-gradient(45deg, #667eea, #764ba2);
      padding: 2px;
      transition: transform 0.2s ease;
      display: block;
    }

    .user-avatar:hover {
      transform: scale(1.05);
    }

    .create-post-input {
      flex: 1;
      border: none;
      outline: none;
      font-size: 0.875rem;
      color: #8e8e8e;
      cursor: pointer;
      padding: 0.75rem;
      border-radius: 20px;
      background: #f5f5f5;
      transition: all 0.2s ease;
    }

    .create-post-input:hover {
      background: #efefef;
    }

    .post-btn {
      background: linear-gradient(45deg, #667eea, #764ba2);
      color: #fff;
      border: none;
      border-radius: 12px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      cursor: pointer;
      font-size: 0.875rem;
      transition: all 0.2s ease;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .post-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }

    /* Instagram-style Posts */
    .post {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 16px;
      margin-bottom: 2rem;
      overflow: hidden;
      animation: fadeInUp 0.6s ease-out;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .post:hover {
      background: rgba(255, 255, 255, 1);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
    }

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

    .post-header {
      display: flex;
      align-items: center;
      padding: 1rem;
      gap: 0.75rem;
    }

    .post-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 3px solid transparent;
      background: linear-gradient(45deg, #667eea, #764ba2);
      padding: 2px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .post-avatar img {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      object-fit: cover;
      display: block;
    }

    .post-user-info {
      flex: 1;
    }

    .post-username {
      font-weight: 600;
      color: #262626;
      font-size: 0.875rem;
      margin: 0;
    }

    .post-location {
      color: #8e8e8e;
      font-size: 0.75rem;
      margin: 0;
    }

    .post-options {
      background: none;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      color: #262626;
      transition: transform 0.2s ease;
    }

    .post-options:hover {
      transform: scale(1.1);
    }

    .post-image {
      width: 100%;
      max-height: 400px;
      object-fit: cover;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .post-image:hover {
      transform: scale(1.02);
    }

    .post-content {
      padding: 1rem;
    }

    .post-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 1rem;
      border-top: 1px solid #efefef;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
    }

    .action-btn {
      background: none;
      border: none;
      font-size: 1.3rem;
      cursor: pointer;
      color: #262626;
      transition: all 0.2s ease;
      position: relative;
    }

    .action-btn:hover {
      transform: scale(1.1);
    }

    .action-btn.liked {
      color: #ed4956;
      animation: heartBeat 0.6s ease;
    }

    @keyframes heartBeat {
      0% { transform: scale(1); }
      25% { transform: scale(1.2); }
      50% { transform: scale(1); }
      75% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }

    .action-btn.saved {
      color: #262626;
    }

    .post-stats {
      font-weight: 600;
      font-size: 0.875rem;
      color: #262626;
    }

    .post-description {
      margin-top: 0.5rem;
      color: #262626;
      line-height: 1.4;
    }

    .post-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin: 0.75rem 0;
    }

    .tag {
      background: #e3f2fd;
      color: #1976d2;
      padding: 0.25rem 0.5rem;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .tag:hover {
      background: #1976d2;
      color: white;
      transform: translateY(-1px);
    }

    .post-time {
      color: #8e8e8e;
      font-size: 0.75rem;
      margin-top: 0.5rem;
    }

    .comments-section {
      padding: 0 1rem 1rem;
      border-top: 1px solid #efefef;
      margin-top: 0.5rem;
    }

    .comment {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0.75rem;
      animation: slideInLeft 0.4s ease;
    }

    @keyframes slideInLeft {
      from {
        opacity: 0;
        transform: translateX(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .comment-avatar {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      object-fit: cover;
    }

    .comment-content {
      flex: 1;
    }

    .comment-username {
      font-weight: 600;
      font-size: 0.875rem;
      color: #262626;
      margin-right: 0.5rem;
    }

    .comment-text {
      font-size: 0.875rem;
      color: #262626;
      display: inline;
    }

    .comment-time {
      color: #8e8e8e;
      font-size: 0.75rem;
      margin-top: 0.25rem;
    }

    .add-comment {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 0;
    }

    .add-comment input {
      flex: 1;
      border: none;
      outline: none;
      font-size: 0.875rem;
      color: #262626;
      background: transparent;
    }

    .add-comment input::placeholder {
      color: #8e8e8e;
    }

    .post-btn-comment {
      background: none;
      border: none;
      color: #0095f6;
      font-weight: 600;
      font-size: 0.875rem;
      cursor: pointer;
      opacity: 0.3;
      transition: opacity 0.2s ease;
    }

    .post-btn-comment.active {
      opacity: 1;
    }

    /* Sidebar */
    .sidebar {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .sidebar-section {
      background: #fff;
      border: 1px solid #dbdbdb;
      border-radius: 8px;
      padding: 1rem;
    }

    .sidebar-title {
      font-weight: 600;
      color: #262626;
      margin-bottom: 1rem;
      font-size: 0.875rem;
    }

    .btn {
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s ease;
      font-size: 0.875rem;
      width: 100%;
      justify-content: center;
    }

    .btn:hover {
      background: #1565c0;
    }

    .btn.secondary {
      background: #fff;
      color: #1976d2;
      border: 1px solid #1976d2;
    }

    .btn.secondary:hover {
      background: #f5f5f5;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.8);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: #fff;
      border-radius: 12px;
      padding: 2rem;
      max-width: 500px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .modal-title {
      font-weight: 600;
      font-size: 1.1rem;
      color: #262626;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #8e8e8e;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #262626;
    }

    .form-input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #dbdbdb;
      border-radius: 8px;
      font-size: 0.875rem;
      outline: none;
    }

    .form-input:focus {
      border-color: #1976d2;
    }

    .form-textarea {
      resize: vertical;
      min-height: 100px;
    }

    .form-select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #dbdbdb;
      border-radius: 8px;
      font-size: 0.875rem;
      outline: none;
      background: #fff;
    }
  </style>
</head>
<body>
  <?php dashboard_background(); ?>

  <!-- Header -->
  <div class="header">
    <div class="header-content">
      <a href="#" class="logo">SConnect</a>
      <div class="header-nav">
        <button class="header-btn" onclick="location.href='dashboard_job_provider.php'">
          <i class="fas fa-home"></i>
        </button>
        <button class="header-btn" onclick="location.href='messages.php'">
          <i class="fas fa-paper-plane"></i>
        </button>
        <button class="header-btn" onclick="location.href='profile.php'">
          <i class="fas fa-user"></i>
        </button>
        <button class="header-btn" onclick="location.href='logout.php'">
          <i class="fas fa-sign-out-alt"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="feed">
      <!-- Create Post Section -->
      <div class="create-post">
        <div class="create-post-header">
          <img src="<?php echo 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=667eea&color=ffffff&size=40'; ?>"
               alt="Your avatar" class="user-avatar">
          <input type="text" class="create-post-input" placeholder="Post a new job opportunity..."
                 onclick="openCreateModal()" readonly>
          <button class="post-btn" onclick="openCreateModal()">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>

      <!-- Job Posts Feed -->
      <?php foreach ($job_items as $index => $job): ?>
      <div class="post" style="animation-delay: <?php echo $index * 0.1; ?>s">
        <div class="post-header">
          <div class="post-avatar">
            <img src="<?php echo 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=667eea&color=ffffff&size=40'; ?>"
                 alt="<?php echo htmlspecialchars($user['name']); ?>">
          </div>
          <div class="post-user-info">
            <div class="post-username"><?php echo htmlspecialchars($user['name']); ?></div>
            <div class="post-location"><?php echo htmlspecialchars($job['location'] ?: 'Remote'); ?></div>
          </div>
          <button class="post-options" onclick="showJobOptions(<?php echo $job['id']; ?>)">
            <i class="fas fa-ellipsis-h"></i>
          </button>
        </div>

        <?php if ($job['image_path']): ?>
        <img src="<?php echo htmlspecialchars($job['image_path']); ?>"
             alt="<?php echo htmlspecialchars($job['title']); ?>"
             class="post-image"
             ondblclick="toggleLike(<?php echo $job['id']; ?>)">
        <?php else: ?>
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 300px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;"
             ondblclick="toggleLike(<?php echo $job['id']; ?>)">
          <i class="fas fa-briefcase"></i>
        </div>
        <?php endif; ?>

        <div class="post-actions">
          <div class="action-buttons">
            <button class="action-btn <?php echo $job['user_liked'] ? 'liked' : ''; ?>" id="like-btn-<?php echo $job['id']; ?>" onclick="toggleLike(<?php echo $job['id']; ?>)">
              <i class="<?php echo $job['user_liked'] ? 'fas' : 'far'; ?> fa-heart"></i>
            </button>
            <button class="action-btn" onclick="focusComment(<?php echo $job['id']; ?>)">
              <i class="far fa-comment"></i>
            </button>
            <button class="action-btn" onclick="shareJob(<?php echo $job['id']; ?>)">
              <i class="far fa-paper-plane"></i>
            </button>
          </div>
          <button class="action-btn" onclick="toggleSave(<?php echo $job['id']; ?>)">
            <i class="far fa-bookmark"></i>
          </button>
        </div>

        <div class="post-content">
          <div class="post-stats">
            <span id="likes-count-<?php echo $job['id']; ?>"><?php echo $job['like_count']; ?></span> likes
          </div>

          <div class="post-description">
            <span class="post-username"><?php echo htmlspecialchars($user['name']); ?></span>
            <strong><?php echo htmlspecialchars($job['title']); ?></strong><br>
            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
          </div>

          <div class="post-tags">
            <span class="tag"><?php echo htmlspecialchars($job['job_type']); ?></span>
            <?php if ($job['budget_min'] || $job['budget_max']): ?>
              <span class="tag">
                <?php if ($job['budget_min'] && $job['budget_max']): ?>
                  $<?php echo number_format($job['budget_min']); ?> - $<?php echo number_format($job['budget_max']); ?>
                <?php elseif ($job['budget_min']): ?>
                  From $<?php echo number_format($job['budget_min']); ?>
                <?php else: ?>
                  Up to $<?php echo number_format($job['budget_max']); ?>
                <?php endif; ?>
              </span>
            <?php endif; ?>
            <?php if ($job['skills_required']): ?>
              <?php foreach (array_slice(explode(',', $job['skills_required']), 0, 3) as $skill): ?>
                <span class="tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <?php if ($job['deadline']): ?>
          <div class="post-time">
            Deadline: <?php echo date('M j, Y', strtotime($job['deadline'])); ?>
          </div>
          <?php endif; ?>

          <div class="post-time">
            <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
          </div>
        </div>

        <div class="comments-section">
          <div id="comments-<?php echo $job['id']; ?>">
            <!-- Sample comments -->
            <div class="comment">
              <img src="https://ui-avatars.com/api/?name=John+Developer&background=28a745&color=ffffff&size=24" alt="Job Seeker" class="comment-avatar">
              <div class="comment-content">
                <span class="comment-username">john_developer</span>
                <span class="comment-text">This looks like a great opportunity! I have 5 years of experience in this field.</span>
                <div class="comment-time">2h</div>
              </div>
            </div>
            <div class="comment">
              <img src="https://ui-avatars.com/api/?name=Sarah+Designer&background=dc3545&color=ffffff&size=24" alt="Senior Dev" class="comment-avatar">
              <div class="comment-content">
                <span class="comment-username">sarah_designer</span>
                <span class="comment-text">Interested! Can we discuss the project timeline?</span>
                <div class="comment-time">1h</div>
              </div>
            </div>
          </div>

          <div class="add-comment">
            <img src="<?php echo 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=667eea&color=ffffff&size=24'; ?>" alt="User" class="comment-avatar">
            <input type="text" placeholder="Add a comment..." id="comment-input-<?php echo $job['id']; ?>"
                   onkeypress="handleCommentKeypress(event, <?php echo $job['id']; ?>)"
                   oninput="toggleCommentButton(<?php echo $job['id']; ?>)">
            <button class="post-btn-comment" id="comment-btn-<?php echo $job['id']; ?>"
                    onclick="addComment(<?php echo $job['id']; ?>)">Post</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-section">
        <div class="sidebar-title">Quick Actions</div>
        <button class="btn" onclick="openCreateModal()">
          <i class="fas fa-plus"></i>
          Post New Job
        </button>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-title">Job Management</div>
        <a href="manage_jobs.php" class="btn secondary">
          <i class="fas fa-cog"></i>
          Manage Jobs
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-title">Find Talent</div>
        <a href="search_talent.php" class="btn secondary">
          <i class="fas fa-search"></i>
          Browse Talent
        </a>
      </div>
    </div>
  </div>

  <!-- Create Job Modal -->
  <div id="createModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Post New Job</div>
        <button class="close-btn" onclick="closeCreateModal()">&times;</button>
      </div>

      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label class="form-label">Job Title</label>
          <input type="text" name="title" class="form-input" required placeholder="e.g., Senior Web Developer">
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-input form-textarea" required placeholder="Describe the job requirements and responsibilities..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Budget Range</label>
          <div style="display: flex; gap: 0.5rem;">
            <input type="number" name="budget_min" class="form-input" placeholder="Min ($)" step="0.01">
            <input type="number" name="budget_max" class="form-input" placeholder="Max ($)" step="0.01">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Location</label>
          <input type="text" name="location" class="form-input" placeholder="e.g., Remote, New York, etc.">
        </div>

        <div class="form-group">
          <label class="form-label">Job Type</label>
          <select name="job_type" class="form-select">
            <option value="contract">Contract</option>
            <option value="full-time">Full-time</option>
            <option value="part-time">Part-time</option>
            <option value="freelance">Freelance</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Requirements</label>
          <textarea name="requirements" class="form-input form-textarea" placeholder="List the key requirements and qualifications..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Skills Required</label>
          <input type="text" name="skills_required" class="form-input" placeholder="e.g., PHP, JavaScript, React">
        </div>

        <div class="form-group">
          <label class="form-label">Deadline</label>
          <input type="date" name="deadline" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label">Attachment (Optional)</label>
          <input type="file" name="attachment" class="form-input" accept="image/*,.pdf,.doc,.docx">
        </div>

        <button type="submit" name="add_job" class="btn">
          <i class="fas fa-plus"></i>
          Post Job
        </button>
      </form>
    </div>
  </div>

  <script>
    // Modal functionality
    function openCreateModal() {
      const modal = document.getElementById('createModal');
      modal.style.display = 'flex';
      modal.style.opacity = '0';
      setTimeout(() => {
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '1';
      }, 10);
    }

    function closeCreateModal() {
      const modal = document.getElementById('createModal');
      modal.style.transition = 'opacity 0.3s ease';
      modal.style.opacity = '0';
      setTimeout(() => {
        modal.style.display = 'none';
      }, 300);
    }

    // Like functionality with backend integration
    function toggleLike(postId) {
      const likeBtn = document.getElementById(`like-btn-${postId}`);
      const likesCount = document.getElementById(`likes-count-${postId}`);
      const icon = likeBtn.querySelector('i');

      // Determine current state
      const isLiked = likeBtn.classList.contains('liked');
      const action = isLiked ? 'unlike' : 'like';

      // Disable button during request
      likeBtn.disabled = true;

      // Send request to backend
      fetch('handle_likes.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          job_posting_id: postId,
          action: action
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update UI based on response
          if (data.liked) {
            likeBtn.classList.add('liked');
            icon.className = 'fas fa-heart';
            createFloatingHeart(likeBtn);
          } else {
            likeBtn.classList.remove('liked');
            icon.className = 'far fa-heart';
          }

          // Update like count
          likesCount.textContent = data.like_count;
        } else {
          console.error('Like error:', data.error);
          showToast(data.error || 'Failed to update like');
        }
      })
      .catch(error => {
        console.error('Network error:', error);
        showToast('Network error. Please try again.');
      })
      .finally(() => {
        // Re-enable button
        likeBtn.disabled = false;
      });
    }

    function createFloatingHeart(element) {
      const heart = document.createElement('div');
      heart.innerHTML = '❤️';
      heart.style.position = 'absolute';
      heart.style.fontSize = '20px';
      heart.style.pointerEvents = 'none';
      heart.style.zIndex = '1000';
      heart.style.animation = 'floatHeart 1s ease-out forwards';

      const rect = element.getBoundingClientRect();
      heart.style.left = rect.left + 'px';
      heart.style.top = rect.top + 'px';

      document.body.appendChild(heart);

      setTimeout(() => {
        document.body.removeChild(heart);
      }, 1000);
    }

    // Add floating heart animation CSS
    const style = document.createElement('style');
    style.textContent = `
      @keyframes floatHeart {
        0% {
          opacity: 1;
          transform: translateY(0) scale(1);
        }
        100% {
          opacity: 0;
          transform: translateY(-50px) scale(1.5);
        }
      }
    `;
    document.head.appendChild(style);

    // Comment functionality
    function addComment(postId) {
      const input = document.getElementById(`comment-input-${postId}`);
      const commentsContainer = document.getElementById(`comments-${postId}`);
      const commentText = input.value.trim();

      if (commentText) {
        const comment = document.createElement('div');
        comment.className = 'comment';
        comment.innerHTML = `
          <img src="<?php echo 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=667eea&color=ffffff&size=24'; ?>" alt="User" class="comment-avatar">
          <div class="comment-content">
            <span class="comment-username">you</span>
            <span class="comment-text">${commentText}</span>
            <div class="comment-time">now</div>
          </div>
        `;

        commentsContainer.appendChild(comment);
        input.value = '';
        toggleCommentButton(postId);

        // Scroll to new comment
        comment.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }

    function handleCommentKeypress(event, postId) {
      if (event.key === 'Enter') {
        addComment(postId);
      }
    }

    function toggleCommentButton(postId) {
      const input = document.getElementById(`comment-input-${postId}`);
      const button = document.getElementById(`comment-btn-${postId}`);

      if (input.value.trim()) {
        button.classList.add('active');
      } else {
        button.classList.remove('active');
      }
    }

    function focusComment(postId) {
      const input = document.getElementById(`comment-input-${postId}`);
      input.focus();
    }

    // Save functionality
    const savedPosts = new Set();

    function toggleSave(postId) {
      const saveBtn = document.querySelector(`[onclick="toggleSave(${postId})"]`);
      const icon = saveBtn.querySelector('i');

      if (savedPosts.has(postId)) {
        savedPosts.delete(postId);
        icon.className = 'far fa-bookmark';
        saveBtn.style.color = '#262626';
      } else {
        savedPosts.add(postId);
        icon.className = 'fas fa-bookmark';
        saveBtn.style.color = '#262626';
      }
    }

    // Share functionality
    function shareJob(postId) {
      if (navigator.share) {
        navigator.share({
          title: 'Job Opportunity',
          text: 'Check out this job opportunity!',
          url: window.location.href + '?job=' + postId
        });
      } else {
        // Fallback - copy to clipboard
        const url = window.location.href + '?job=' + postId;
        navigator.clipboard.writeText(url).then(() => {
          showToast('Link copied to clipboard!');
        });
      }
    }

    function showJobOptions(postId) {
      // Show job options menu
      const options = ['Edit Job', 'Delete Job', 'View Applications', 'Boost Post'];
      const menu = document.createElement('div');
      menu.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        z-index: 1000;
        min-width: 200px;
        animation: slideIn 0.3s ease;
      `;

      options.forEach(option => {
        const item = document.createElement('div');
        item.textContent = option;
        item.style.cssText = `
          padding: 1rem;
          cursor: pointer;
          border-bottom: 1px solid #efefef;
          transition: background 0.2s ease;
        `;
        item.onmouseover = () => item.style.background = '#f5f5f5';
        item.onmouseout = () => item.style.background = 'white';
        item.onclick = () => {
          handleJobOption(option, postId);
          document.body.removeChild(menu);
        };
        menu.appendChild(item);
      });

      document.body.appendChild(menu);

      // Close on click outside
      setTimeout(() => {
        document.addEventListener('click', function closeMenu(e) {
          if (!menu.contains(e.target)) {
            document.body.removeChild(menu);
            document.removeEventListener('click', closeMenu);
          }
        });
      }, 100);
    }

    function handleJobOption(option, postId) {
      switch(option) {
        case 'Edit Job':
          console.log('Edit job:', postId);
          break;
        case 'Delete Job':
          if (confirm('Are you sure you want to delete this job?')) {
            console.log('Delete job:', postId);
          }
          break;
        case 'View Applications':
          console.log('View applications for job:', postId);
          break;
        case 'Boost Post':
          console.log('Boost post:', postId);
          break;
      }
    }

    function showToast(message) {
      const toast = document.createElement('div');
      toast.textContent = message;
      toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #262626;
        color: white;
        padding: 1rem 2rem;
        border-radius: 8px;
        z-index: 1000;
        animation: slideUp 0.3s ease;
      `;

      document.body.appendChild(toast);

      setTimeout(() => {
        toast.style.animation = 'slideDown 0.3s ease';
        setTimeout(() => {
          document.body.removeChild(toast);
        }, 300);
      }, 2000);
    }

    // Initialize like states on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Like states are already loaded from PHP, no additional initialization needed
      console.log('Dashboard loaded successfully');
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('createModal');
      if (event.target === modal) {
        closeCreateModal();
      }
    }

    // Add slide animations CSS
    const animationStyle = document.createElement('style');
    animationStyle.textContent = `
      @keyframes slideIn {
        from { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
        to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
      }
      @keyframes slideUp {
        from { opacity: 0; transform: translate(-50%, 20px); }
        to { opacity: 1; transform: translate(-50%, 0); }
      }
      @keyframes slideDown {
        from { opacity: 1; transform: translate(-50%, 0); }
        to { opacity: 0; transform: translate(-50%, 20px); }
      }
    `;
    document.head.appendChild(animationStyle);
  </script>

  <!-- AI Tools: Chatbot & CV Scanner -->
  <?php include_ai_tools(); ?>
</body>
</html>
