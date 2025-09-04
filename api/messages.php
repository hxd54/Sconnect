<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
$user = current_user();
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/background.php';

// Fetch all conversations for the current user
$conversations = $pdo->prepare("
    SELECT c.*, 
           CASE 
               WHEN c.user1_id = ? THEN u2.name 
               ELSE u1.name 
           END as partner_name,
           CASE 
               WHEN c.user1_id = ? THEN c.user2_id 
               ELSE c.user1_id 
           END as partner_id,
           CASE 
               WHEN c.user1_id = ? THEN u2.role 
               ELSE u1.role 
           END as partner_role,
           (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = 0) as unread_count
    FROM conversations c
    JOIN users u1 ON c.user1_id = u1.id
    JOIN users u2 ON c.user2_id = u2.id
    WHERE c.user1_id = ? OR c.user2_id = ?
    ORDER BY c.last_message_at DESC
");
$conversations->execute([$user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
$chats = $conversations->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages – Sconnect</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .header h1 { margin: 0; font-size: 2rem; }
        .back-btn {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }
        .back-btn:hover { transform: translateX(-5px); }
        .conversations {
            padding: 1rem;
        }
        .conversation {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        .conversation:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }
        .conversation-info {
            flex: 1;
        }
        .partner-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .role-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            background: #e5e7eb;
            color: #374151;
        }
        .role-badge.job_seeker { background: #dbeafe; color: #1e40af; }
        .role-badge.job_provider { background: #dcfce7; color: #166534; }
        .last-message {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }
        .conversation-meta {
            text-align: right;
        }
        .unread-badge {
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .time {
            font-size: 0.8rem;
            color: #9ca3af;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="<?= $user['role'] === 'job_seeker' ? 'dashboard_job_seeker.php' : 'dashboard_job_provider.php' ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-comments"></i> Messages</h1>
        </div>
        
        <div class="conversations">
            <?php if (empty($chats)): ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>No conversations yet</h3>
                    <p>Start connecting with other users to begin messaging!</p>
                    <a href="<?= $user['role'] === 'job_seeker' ? 'browse_jobs.php' : 'search_talent.php' ?>" 
                       style="background: #4f46e5; color: white; padding: 0.8rem 1.5rem; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 1rem;">
                        <i class="fas fa-search"></i> 
                        <?= $user['role'] === 'job_seeker' ? 'Browse Jobs' : 'Find Talent' ?>
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($chats as $chat): ?>
                    <a href="chat.php?to=<?= $chat['partner_id'] ?>" class="conversation">
                        <div class="avatar">
                            <?= strtoupper(substr($chat['partner_name'], 0, 2)) ?>
                        </div>
                        <div class="conversation-info">
                            <div class="partner-name">
                                <?= htmlspecialchars($chat['partner_name']) ?>
                                <span class="role-badge <?= $chat['partner_role'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $chat['partner_role'])) ?>
                                </span>
                            </div>
                            <p class="last-message">
                                <?= htmlspecialchars(substr($chat['last_message'] ?: 'No messages yet', 0, 60)) ?>
                                <?= strlen($chat['last_message'] ?: '') > 60 ? '...' : '' ?>
                            </p>
                        </div>
                        <div class="conversation-meta">
                            <?php if ($chat['unread_count'] > 0): ?>
                                <div class="unread-badge"><?= $chat['unread_count'] ?></div>
                            <?php endif; ?>
                            <div class="time">
                                <?= date('M j', strtotime($chat['last_message_at'])) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
