<?php
// get_skills.php - AJAX endpoint for loading skills based on selected categories
require_once __DIR__ . '/inc/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['categories']) || !is_array($input['categories'])) {
    echo json_encode(['error' => 'Invalid categories']);
    exit;
}

$categories = array_filter(array_map('intval', $input['categories']));

if (empty($categories)) {
    echo json_encode([]);
    exit;
}

try {
    $placeholders = str_repeat('?,', count($categories) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.name, sc.category_id
        FROM skills s
        JOIN skill_categories sc ON s.id = sc.skill_id
        WHERE sc.category_id IN ($placeholders) AND s.is_active = 1
        ORDER BY s.name
    ");
    $stmt->execute($categories);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($skills);
} catch (PDOException $e) {
    http_response_code(500);
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        echo json_encode(['error' => 'Database tables not found. Please run database setup first.']);
    } else {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>
