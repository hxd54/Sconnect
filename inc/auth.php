<?php
// inc/auth.php
session_start();
require_once __DIR__ . '/db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    global $pdo;
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Handle legacy role migration
    if ($user && in_array($user['role'], ['wager', 'student', 'seeker'])) {
        $new_role = match($user['role']) {
            'wager' => 'job_seeker',
            'student' => 'job_seeker',
            'seeker' => 'job_provider',
            default => $user['role']
        };

        // Update role in database
        $update_stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $update_stmt->execute([$new_role, $user['id']]);
        $user['role'] = $new_role;
    }

    return $user;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_role($role) {
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        header('Location: login.php');
        exit;
    }
}
?>