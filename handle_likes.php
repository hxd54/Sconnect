<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST requests for like/unlike
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['action'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }

    $action = $input['action']; // 'like' or 'unlike'

    // Determine post type and ID
    $job_posting_id = null;
    $post_id = null;
    $like_type = '';

    if (isset($input['job_posting_id'])) {
        $job_posting_id = (int)$input['job_posting_id'];
        $like_type = 'job_posting';
    } elseif (isset($input['post_id'])) {
        $post_id = (int)$input['post_id'];
        $like_type = 'post';
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing post ID']);
        exit;
    }
    
    try {
        if ($action === 'like') {
            // Check if user already liked this post
            if ($like_type === 'job_posting') {
                $check_stmt = $pdo->prepare('SELECT id FROM post_likes WHERE user_id = ? AND job_posting_id = ? AND like_type = "job_posting"');
                $check_stmt->execute([$user_id, $job_posting_id]);
            } else {
                $check_stmt = $pdo->prepare('SELECT id FROM post_likes WHERE user_id = ? AND post_id = ? AND like_type = "post"');
                $check_stmt->execute([$user_id, $post_id]);
            }

            if ($check_stmt->rowCount() > 0) {
                echo json_encode(['error' => 'Already liked', 'liked' => true]);
                exit;
            }

            // Add like
            if ($like_type === 'job_posting') {
                $stmt = $pdo->prepare('INSERT INTO post_likes (user_id, job_posting_id, like_type) VALUES (?, ?, "job_posting")');
                $stmt->execute([$user_id, $job_posting_id]);

                // Get updated like count
                $count_stmt = $pdo->prepare('SELECT COUNT(*) as count FROM post_likes WHERE job_posting_id = ? AND like_type = "job_posting"');
                $count_stmt->execute([$job_posting_id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO post_likes (user_id, post_id, like_type) VALUES (?, ?, "post")');
                $stmt->execute([$user_id, $post_id]);

                // Get updated like count
                $count_stmt = $pdo->prepare('SELECT COUNT(*) as count FROM post_likes WHERE post_id = ? AND like_type = "post"');
                $count_stmt->execute([$post_id]);
            }

            $like_count = $count_stmt->fetch()['count'];

            echo json_encode([
                'success' => true,
                'liked' => true,
                'like_count' => $like_count
            ]);

        } elseif ($action === 'unlike') {
            // Remove like
            if ($like_type === 'job_posting') {
                $stmt = $pdo->prepare('DELETE FROM post_likes WHERE user_id = ? AND job_posting_id = ? AND like_type = "job_posting"');
                $stmt->execute([$user_id, $job_posting_id]);

                // Get updated like count
                $count_stmt = $pdo->prepare('SELECT COUNT(*) as count FROM post_likes WHERE job_posting_id = ? AND like_type = "job_posting"');
                $count_stmt->execute([$job_posting_id]);
            } else {
                $stmt = $pdo->prepare('DELETE FROM post_likes WHERE user_id = ? AND post_id = ? AND like_type = "post"');
                $stmt->execute([$user_id, $post_id]);

                // Get updated like count
                $count_stmt = $pdo->prepare('SELECT COUNT(*) as count FROM post_likes WHERE post_id = ? AND like_type = "post"');
                $count_stmt->execute([$post_id]);
            }

            $like_count = $count_stmt->fetch()['count'];

            echo json_encode([
                'success' => true,
                'liked' => false,
                'like_count' => $like_count
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Handle GET requests for like status
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['job_posting_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing job_posting_id parameter']);
        exit;
    }
    
    $job_posting_id = (int)$_GET['job_posting_id'];
    
    try {
        // Check if user liked this post
        $like_stmt = $pdo->prepare('SELECT id FROM post_likes WHERE user_id = ? AND job_posting_id = ? AND like_type = "job_posting"');
        $like_stmt->execute([$user_id, $job_posting_id]);
        $liked = $like_stmt->rowCount() > 0;
        
        // Get total like count
        $count_stmt = $pdo->prepare('SELECT COUNT(*) as count FROM post_likes WHERE job_posting_id = ? AND like_type = "job_posting"');
        $count_stmt->execute([$job_posting_id]);
        $like_count = $count_stmt->fetch()['count'];
        
        echo json_encode([
            'liked' => $liked,
            'like_count' => $like_count
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
