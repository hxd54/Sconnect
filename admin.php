<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
if ($user['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/db.php';

// Handle suspend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suspend'])) {
    $user_id = $_POST['user_id'];
    $reason = $_POST['reason'] ?? 'No reason provided';
    $pdo->prepare("INSERT INTO suspensions (user_id, admin_id, reason) VALUES (?, ?, ?)")
        ->execute([$user_id, $user['id'], $reason]);
    $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_user_id, details) VALUES (?, 'suspend', ?, ?)")
        ->execute([$user['id'], $user_id, $reason]);
}

// Handle promote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote'])) {
    $user_id = $_POST['user_id'];
    $pdo->prepare("UPDATE users SET role='admin' WHERE id=?")->execute([$user_id]);
    $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_user_id, details) VALUES (?, 'promote', ?, 'Promoted to admin')")
        ->execute([$user['id'], $user_id]);
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard – Sconnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: 'Inter', Arial, sans-serif; background: #f9fafb; margin:0; }
    .container { max-width: 900px; margin: 2rem auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 2rem 1.5rem; }
    h2 { color: #4f46e5; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 0.7rem; border-bottom: 1px solid #e0e7ff; text-align: left; }
    th { background: #eef2ff; }
    .btn { background: #4f46e5; color: #fff; border: none; border-radius: 8px; padding: 0.4rem 1rem; font-weight: bold; cursor: pointer; }
    @media (max-width: 900px) { .container { margin: 0.5rem; padding: 1rem 0.5rem; } table, th, td { font-size: 0.95em; } }
  </style>
</head>
<body>
  <div class="container">
    <h2>Admin Dashboard</h2>
    <table>
      <tr>
        <th>Name</th><th>Email</th><th>Role</th><th>Location</th><th>Rating</th><th>Certified</th><th>Actions</th>
      </tr>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td><?= htmlspecialchars($u['location']) ?></td>
          <td>4.8</td>
          <td><?= $u['proof_path'] ? "Yes" : "No" ?></td>
          <td>
            <form method="post" style="display:inline;">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="text" name="reason" placeholder="Reason" style="width:80px;">
              <button class="btn" name="suspend">Suspend</button>
            </form>
            <form method="post" style="display:inline;">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button class="btn" name="promote">Promote</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>