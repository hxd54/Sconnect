<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
if ($user['role'] !== 'student') { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/db.php';

$errors = [];
$success = null;

// Handle new achievement submission for student (e.g., coursework, certifications)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_achievement'])) {
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
      $dest = 'uploads/' . uniqid('ach_', true) . ($ext ? ('.' . $ext) : '');
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
    $success = 'Achievement added.';
  }
}

// Handle delete
if (isset($_POST['delete_achievement'])) {
  $delId = (int)$_POST['delete_id'];
  $pdo->prepare('DELETE FROM portfolio WHERE id = ? AND user_id = ?')->execute([$delId, $user['id']]);
  $success = 'Achievement deleted.';
}

// Fetch achievements
$stmt = $pdo->prepare('SELECT * FROM portfolio WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$achievements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard - SConnect</title>
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
      background: #fafafa;
      margin: 0; 
      min-height: 100vh;
    }

    /* Instagram Header */
    .header {
      background: #fff;
      border-bottom: 1px solid #dbdbdb;
      padding: 1rem;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
      background: #fff;
      border: 1px solid #dbdbdb;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 2rem;
    }
    
    .create-post-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }
    
    .user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .create-post-input {
      flex: 1;
      border: none;
      outline: none;
      font-size: 0.875rem;
      color: #8e8e8e;
      cursor: pointer;
    }
    
    .post-btn {
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-weight: 600;
      cursor: pointer;
      font-size: 0.875rem;
    }

    /* Achievement Grid */
    .achievements-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.25rem;
      margin-bottom: 2rem;
    }
    
    @media (max-width: 768px) {
      .achievements-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    .achievement-item {
      aspect-ratio: 1;
      position: relative;
      overflow: hidden;
      border-radius: 4px;
      cursor: pointer;
      background: #f5f5f5;
    }
    
    .achievement-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .achievement-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.5);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.2s ease;
      padding: 1rem;
      text-align: center;
    }
    
    .achievement-item:hover .achievement-overlay {
      opacity: 1;
    }
    
    .achievement-title {
      font-weight: 600;
      font-size: 0.875rem;
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
  </style>
</head>
<body>
  <!-- Header -->
  <div class="header">
    <div class="header-content">
      <a href="#" class="logo">SConnect</a>
      <div class="header-nav">
        <button class="header-btn" onclick="location.href='dashboard_student.php'">
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
          <img src="<?php echo htmlspecialchars($user['profile_picture'] ?: 'default-avatar.png'); ?>" 
               alt="Your avatar" class="user-avatar">
          <input type="text" class="create-post-input" placeholder="Share your academic achievements..." 
                 onclick="openCreateModal()" readonly>
          <button class="post-btn" onclick="openCreateModal()">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>

      <!-- Achievements Grid -->
      <div class="achievements-grid">
        <?php foreach ($achievements as $achievement): ?>
        <div class="achievement-item" onclick="viewAchievement(<?php echo $achievement['id']; ?>)">
          <?php if ($achievement['image_path']): ?>
            <img src="<?php echo htmlspecialchars($achievement['image_path']); ?>" 
                 alt="<?php echo htmlspecialchars($achievement['title']); ?>">
          <?php else: ?>
            <div style="background: linear-gradient(45deg, #1e3a8a, #3b82f6); display: flex; align-items: center; justify-content: center; height: 100%; color: white;">
              <i class="fas fa-graduation-cap" style="font-size: 2rem;"></i>
            </div>
          <?php endif; ?>
          <div class="achievement-overlay">
            <div class="achievement-title"><?php echo htmlspecialchars($achievement['title']); ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-section">
        <div class="sidebar-title">Quick Actions</div>
        <button class="btn" onclick="openCreateModal()">
          <i class="fas fa-plus"></i>
          Add Achievement
        </button>
      </div>
      
      <div class="sidebar-section">
        <div class="sidebar-title">Academic Portfolio</div>
        <a href="portfolio.php" class="btn secondary">
          <i class="fas fa-graduation-cap"></i>
          View Portfolio
        </a>
      </div>
      
      <div class="sidebar-section">
        <div class="sidebar-title">Study Resources</div>
        <a href="resources.php" class="btn secondary">
          <i class="fas fa-book"></i>
          Study Materials
        </a>
      </div>
    </div>
  </div>

  <!-- Create Achievement Modal -->
  <div id="createModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Add Academic Achievement</div>
        <button class="close-btn" onclick="closeCreateModal()">&times;</button>
      </div>
      
      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-input" required placeholder="e.g., Dean's List, Certification, Project">
        </div>
        
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-input form-textarea" placeholder="Describe your achievement..."></textarea>
        </div>
        
        <div class="form-group">
          <label class="form-label">Attachment (Certificate, Image, or Document)</label>
          <input type="file" name="attachment" class="form-input" accept="image/*,.pdf,.doc,.docx">
        </div>
        
        <button type="submit" name="add_achievement" class="btn">
          <i class="fas fa-plus"></i>
          Add Achievement
        </button>
      </form>
    </div>
  </div>

  <script>
    function openCreateModal() {
      document.getElementById('createModal').style.display = 'flex';
    }
    
    function closeCreateModal() {
      document.getElementById('createModal').style.display = 'none';
    }
    
    function viewAchievement(id) {
      // Add view achievement functionality
      console.log('View achievement:', id);
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('createModal');
      if (event.target === modal) {
        closeCreateModal();
      }
    }
  </script>
</body>
</html>
