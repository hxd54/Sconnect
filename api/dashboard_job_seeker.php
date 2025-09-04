<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
if ($user['role'] !== 'job_seeker') {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/inc/components.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/chatbot.php';
require_once __DIR__ . '/inc/background.php';

$errors = [];
$success = null;

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $skills = trim($_POST['skills'] ?? '');
  $experience_level = $_POST['experience_level'] ?? 'entry';
  $availability = $_POST['availability'] ?? 'full-time';
  $hourly_rate = !empty($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : null;
  $location = trim($_POST['location'] ?? '');

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
    $stmt = $pdo->prepare('INSERT INTO portfolio (user_id, title, description, skills, experience_level, availability, hourly_rate, location, image_path, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user['id'], $title, $description, $skills, $experience_level, $availability, $hourly_rate, $location ?: null, $image_path, $file_path]);
    $success = 'Post created successfully.';
  }
}

// Fetch job postings with like counts and user's like status
try {
    $stmt = $pdo->prepare("
        SELECT jp.*, u.name as provider_name, 'job_posting' as post_type,
               COUNT(DISTINCT pl.id) as like_count,
               MAX(CASE WHEN pl.user_id = ? THEN 1 ELSE 0 END) as user_liked
        FROM job_postings jp
        JOIN users u ON jp.user_id = u.id
        LEFT JOIN post_likes pl ON jp.id = pl.job_posting_id AND pl.like_type = 'job_posting'
        GROUP BY jp.id
        ORDER BY jp.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $job_posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
    $job_posts = [];
}

// Fetch user's own posts from portfolio
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.name as provider_name, 'portfolio' as post_type,
               COUNT(DISTINCT pl.id) as like_count,
               MAX(CASE WHEN pl.user_id = ? THEN 1 ELSE 0 END) as user_liked
        FROM portfolio p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN post_likes pl ON p.id = pl.post_id AND pl.like_type = 'post'
        WHERE p.user_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $user_posts = $stmt->fetchAll();

    // Combine and sort all posts
    $all_posts = array_merge($job_posts, $user_posts);
    usort($all_posts, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $job_posts = $all_posts;
} catch (PDOException $e) {
    // If portfolio table doesn't exist, just use job posts
    $errors[] = "Portfolio table not found. Only showing job postings.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Job Seeker Dashboard - SConnect</title>
  <link href="https://fonts.googleapis.com/css2?family=Billabong&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      background-attachment: fixed;
      margin: 0;
      min-height: 100vh;
      color: #262626;
    }

    /* Instagram Header */
    .header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      padding: 1rem 0;
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
      padding: 0 1rem;
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
      background: rgba(245, 245, 245, 0.8);
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

    .feed {
      max-width: 614px;
    }



    /* Stories Section */
    .stories-section {
      background: #fff;
      border: 1px solid #dbdbdb;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 2rem;
    }
    
    .stories-container {
      display: flex;
      gap: 1rem;
      overflow-x: auto;
      padding: 0.5rem 0;
    }
    
    .story {
      flex-shrink: 0;
      text-align: center;
      cursor: pointer;
    }
    
    .story-avatar {
      width: 66px;
      height: 66px;
      border-radius: 50%;
      background: linear-gradient(45deg, #1e3a8a, #3b82f6);
      padding: 2px;
      margin-bottom: 0.5rem;
    }
    
    .story-avatar img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      border: 2px solid #fff;
      object-fit: cover;
    }
    
    .story-username {
      font-size: 0.75rem;
      color: #262626;
      max-width: 66px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* Post Styles */
    .post {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 16px;
      margin-bottom: 2rem;
      overflow: hidden;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .post:hover {
      background: rgba(255, 255, 255, 1);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
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
    }

    .post-content {
      padding: 0 1rem 1rem;
    }
    
    .post-title {
      font-weight: 600;
      color: #262626;
      margin-bottom: 0.5rem;
    }
    
    .post-description {
      color: #262626;
      line-height: 1.4;
      margin-bottom: 1rem;
    }
    
    .post-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }
    
    .tag {
      background: #e3f2fd;
      color: #1976d2;
      padding: 0.25rem 0.5rem;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .post-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 1rem;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
    }

    .action-btn {
      background: none;
      border: none;
      color: #262626;
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 50%;
      transition: all 0.2s ease;
    }

    .action-btn:hover {
      background: rgba(0, 0, 0, 0.05);
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

    .post-stats {
      font-weight: 600;
      color: #262626;
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
    }

    .post-time {
      color: #8e8e8e;
      font-size: 0.75rem;
      text-transform: uppercase;
      margin-top: 0.5rem;
    }

    .post-image {
      width: 100%;
      height: auto;
      display: block;
      cursor: pointer;
    }

    .post-options {
      background: none;
      border: none;
      color: #262626;
      font-size: 1rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 50%;
      transition: all 0.2s ease;
    }

    .post-options:hover {
      background: rgba(0, 0, 0, 0.05);
    }
    
    /* Sidebar */
    .sidebar {
      position: sticky;
      top: 100px;
      height: fit-content;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .sidebar-section {
      margin-bottom: 2rem;
    }

    .sidebar-section:last-child {
      margin-bottom: 0;
    }
    
    .sidebar-title {
      font-weight: 600;
      color: #262626;
      margin-bottom: 1rem;
      font-size: 0.875rem;
    }

    .btn {
      background: linear-gradient(45deg, #667eea, #764ba2);
      color: #fff;
      border: none;
      border-radius: 12px;
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
      margin-bottom: 0.75rem;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }

    .btn.secondary {
      background: rgba(255, 255, 255, 0.9);
      color: #667eea;
      border: 2px solid #667eea;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }

    .btn.secondary:hover {
      background: rgba(102, 126, 234, 0.1);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
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

    /* Create Post Section */
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
      padding: 0.75rem 1rem;
      border-radius: 25px;
      background: rgba(245, 245, 245, 0.8);
      transition: all 0.2s ease;
    }

    .create-post-input:hover {
      background: rgba(239, 239, 239, 0.9);
      transform: scale(1.01);
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

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 2rem;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
      animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .modal-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #262626;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 2rem;
      color: #8e8e8e;
      cursor: pointer;
      transition: color 0.2s ease;
    }

    .close-btn:hover {
      color: #262626;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      display: block;
      font-weight: 600;
      color: #262626;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .form-input, .form-textarea, .form-select {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      font-size: 0.9rem;
      transition: all 0.2s ease;
      background: rgba(255, 255, 255, 0.8);
    }

    .form-input:focus, .form-textarea:focus, .form-select:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      background: rgba(255, 255, 255, 1);
    }

    .form-textarea {
      resize: vertical;
      min-height: 100px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }

    .submit-btn {
      background: linear-gradient(45deg, #667eea, #764ba2);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 1rem 2rem;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.2s ease;
      width: 100%;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
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
        <button class="header-btn" onclick="location.href='dashboard_job_seeker.php'" title="Home">
          <i class="fas fa-home"></i>
        </button>
        <button class="header-btn" onclick="location.href='messages.php'" title="Messages">
          <i class="fas fa-paper-plane"></i>
        </button>
        <button class="header-btn" onclick="openCreateModal()" title="Create Post">
          <i class="fas fa-plus-square"></i>
        </button>
        <button class="header-btn" onclick="location.href='profile.php'" title="Profile">
          <i class="fas fa-user"></i>
        </button>
        <button class="header-btn" onclick="location.href='logout.php'" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="feed">
      <!-- Error Display -->
      <?php if ($errors): ?>
        <div style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
          <strong>Error:</strong> <?= implode('<br>', $errors) ?>
        </div>
      <?php endif; ?>

      <!-- Success Display -->
      <?php if ($success): ?>
        <div style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
          <strong>Success:</strong> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <!-- Create Post Section -->
      <div class="create-post">
        <div class="create-post-header">
          <img src="<?php echo 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=667eea&color=ffffff&size=40'; ?>"
               alt="Your avatar" class="user-avatar">
          <input type="text" class="create-post-input" placeholder="Share your skills and experience..."
                 onclick="openCreateModal()" readonly>
          <button class="post-btn" onclick="openCreateModal()">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>

      <!-- Job Posts Feed -->
      <?php if (empty($job_posts)): ?>
        <div style="text-align: center; padding: 3rem; background: rgba(255,255,255,0.95); border-radius: 16px; margin-bottom: 2rem;">
          <i class="fas fa-briefcase" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
          <h3 style="color: #262626; margin-bottom: 0.5rem;">No Job Postings Yet</h3>
          <p style="color: #8e8e8e;">Check back later for new job opportunities!</p>
        </div>
      <?php endif; ?>
      <?php foreach ($job_posts as $index => $post): ?>
      <div class="post" style="animation-delay: <?php echo $index * 0.1; ?>s">
        <div class="post-header">
          <img src="<?php echo 'https://ui-avatars.com/api/?name=' . urlencode($post['provider_name']) . '&background=667eea&color=ffffff&size=40'; ?>"
               alt="<?php echo htmlspecialchars($post['provider_name']); ?>" class="post-avatar">
          <div class="post-user-info">
            <div class="post-username"><?php echo htmlspecialchars($post['provider_name']); ?></div>
            <div class="post-location"><?php echo htmlspecialchars($post['location'] ?? 'Remote'); ?></div>
          </div>
          <button class="post-options">
            <i class="fas fa-ellipsis-h"></i>
          </button>
        </div>

        <?php if ($post['image_path']): ?>
        <img src="<?php echo htmlspecialchars($post['image_path']); ?>"
             alt="<?php echo htmlspecialchars($post['title']); ?>"
             class="post-image"
             ondblclick="toggleLike(<?php echo $post['id']; ?>, '<?php echo $post['post_type']; ?>')">
        <?php else: ?>
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 300px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;"
             ondblclick="toggleLike(<?php echo $post['id']; ?>, '<?php echo $post['post_type']; ?>')">
          <i class="fas fa-<?php echo $post['post_type'] === 'portfolio' ? 'user' : 'briefcase'; ?>"></i>
        </div>
        <?php endif; ?>

        <div class="post-actions">
          <div class="action-buttons">
            <button class="action-btn <?php echo $post['user_liked'] ? 'liked' : ''; ?>" id="like-btn-<?php echo $post['id']; ?>-<?php echo $post['post_type']; ?>" onclick="toggleLike(<?php echo $post['id']; ?>, '<?php echo $post['post_type']; ?>')">
              <i class="<?php echo $post['user_liked'] ? 'fas' : 'far'; ?> fa-heart"></i>
            </button>
            <button class="action-btn" onclick="focusComment(<?php echo $post['id']; ?>)">
              <i class="far fa-comment"></i>
            </button>
            <button class="action-btn" onclick="sharePost(<?php echo $post['id']; ?>)">
              <i class="far fa-paper-plane"></i>
            </button>
          </div>
          <button class="action-btn" onclick="toggleSave(<?php echo $post['id']; ?>)">
            <i class="far fa-bookmark"></i>
          </button>
        </div>

        <div class="post-content">
          <div class="post-stats">
            <span id="likes-count-<?php echo $post['id']; ?>-<?php echo $post['post_type']; ?>"><?php echo $post['like_count']; ?></span> likes
          </div>

          <div class="post-description">
            <span class="post-username"><?php echo htmlspecialchars($post['provider_name']); ?></span>
            <strong><?php echo htmlspecialchars($post['title']); ?></strong><br>
            <?php echo nl2br(htmlspecialchars(substr($post['description'], 0, 200))); ?><?php echo strlen($post['description']) > 200 ? '...' : ''; ?>
          </div>

          <div class="post-tags">
            <?php if ($post['post_type'] === 'job_posting'): ?>
              <span class="tag"><?php echo htmlspecialchars($post['job_type'] ?? 'Job'); ?></span>
              <?php if ($post['budget_min'] || $post['budget_max']): ?>
                <span class="tag">
                  <?php if ($post['budget_min'] && $post['budget_max']): ?>
                    $<?php echo number_format($post['budget_min']); ?> - $<?php echo number_format($post['budget_max']); ?>
                  <?php elseif ($post['budget_min']): ?>
                    From $<?php echo number_format($post['budget_min']); ?>
                  <?php else: ?>
                    Up to $<?php echo number_format($post['budget_max']); ?>
                  <?php endif; ?>
                </span>
              <?php endif; ?>
            <?php else: ?>
              <span class="tag">Portfolio</span>
              <?php if ($post['experience_level']): ?>
                <span class="tag"><?php echo ucfirst($post['experience_level']); ?> Level</span>
              <?php endif; ?>
              <?php if ($post['availability']): ?>
                <span class="tag"><?php echo ucfirst($post['availability']); ?></span>
              <?php endif; ?>
              <?php if ($post['hourly_rate']): ?>
                <span class="tag">$<?php echo number_format($post['hourly_rate'], 2); ?>/hr</span>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ($post['location']): ?>
              <span class="tag"><?php echo htmlspecialchars($post['location']); ?></span>
            <?php endif; ?>
          </div>

          <div class="post-time">
            <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
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
          Create Post
        </button>
        <a href="browse_jobs.php" class="btn secondary">
          <i class="fas fa-search"></i>
          Browse Jobs
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-title">Your Portfolio</div>
        <a href="my_portfolio.php" class="btn secondary">
          <i class="fas fa-user"></i>
          View Portfolio
        </a>
      </div>
    </div>
  </div>

  <!-- Create Post Modal -->
  <div id="createModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Create New Post</div>
        <button class="close-btn" onclick="closeCreateModal()">&times;</button>
      </div>

      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label class="form-label">Post Title</label>
          <input type="text" name="title" class="form-input" placeholder="e.g., Experienced Web Developer Available" required>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-textarea" placeholder="Describe your skills, experience, and what you're looking for..." required></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Skills</label>
            <input type="text" name="skills" class="form-input" placeholder="e.g., JavaScript, React, Node.js">
          </div>
          <div class="form-group">
            <label class="form-label">Experience Level</label>
            <select name="experience_level" class="form-select">
              <option value="entry">Entry Level</option>
              <option value="intermediate">Intermediate</option>
              <option value="senior">Senior</option>
              <option value="expert">Expert</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Availability</label>
            <select name="availability" class="form-select">
              <option value="full-time">Full Time</option>
              <option value="part-time">Part Time</option>
              <option value="contract">Contract</option>
              <option value="freelance">Freelance</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Hourly Rate ($)</label>
            <input type="number" name="hourly_rate" class="form-input" placeholder="25" min="0" step="0.01">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Location</label>
          <input type="text" name="location" class="form-input" placeholder="e.g., New York, NY or Remote">
        </div>

        <div class="form-group">
          <label class="form-label">Attachment (Optional)</label>
          <input type="file" name="attachment" class="form-input" accept="image/*,.pdf,.doc,.docx">
          <small style="color: #8e8e8e; font-size: 0.8rem;">Max 10MB. Images, PDFs, or documents only.</small>
        </div>

        <button type="submit" name="add_post" class="submit-btn">
          <i class="fas fa-plus"></i>
          Create Post
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
    function toggleLike(postId, postType = 'job_posting') {
      const likeBtn = document.getElementById(`like-btn-${postId}-${postType}`);
      const likesCount = document.getElementById(`likes-count-${postId}-${postType}`);
      const icon = likeBtn.querySelector('i');

      // Determine current state
      const isLiked = likeBtn.classList.contains('liked');
      const action = isLiked ? 'unlike' : 'like';

      // Disable button during request
      likeBtn.disabled = true;

      // Prepare request data based on post type
      const requestData = {
        action: action
      };

      if (postType === 'job_posting') {
        requestData.job_posting_id = postId;
      } else {
        requestData.post_id = postId;
      }

      // Send request to backend
      fetch('handle_likes.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
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

    function sharePost(postId) {
      if (navigator.share) {
        navigator.share({
          title: 'Check out this post',
          text: 'Check out this post on SConnect!',
          url: window.location.href + '?post=' + postId
        });
      } else {
        // Fallback - copy to clipboard
        const url = window.location.href + '?post=' + postId;
        navigator.clipboard.writeText(url).then(() => {
          showToast('Link copied to clipboard!');
        });
      }
    }

    function toggleSave(postId) {
      // Placeholder for save functionality
      showToast('Save functionality coming soon!');
    }

    function focusComment(postId) {
      // Placeholder for comment functionality
      showToast('Comment functionality coming soon!');
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

    // Add slide animations CSS
    const animationStyle = document.createElement('style');
    animationStyle.textContent = `
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

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('createModal');
      if (event.target === modal) {
        closeCreateModal();
      }
    }

    // Add fade-in animation for posts
    document.addEventListener('DOMContentLoaded', function() {
      const posts = document.querySelectorAll('.post');
      posts.forEach((post, index) => {
        post.style.opacity = '0';
        post.style.transform = 'translateY(20px)';
        setTimeout(() => {
          post.style.transition = 'all 0.6s ease';
          post.style.opacity = '1';
          post.style.transform = 'translateY(0)';
        }, index * 100);
      });
    });
  </script>

  <!-- AI Tools: Chatbot & CV Scanner -->
  <?php include_ai_tools(); ?>
</body>
</html>
