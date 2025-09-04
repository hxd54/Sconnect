<?php
// Test page to verify job seeker registration form functionality
require_once __DIR__ . '/inc/db.php';

$test_results = [];

// Test 1: Check if job categories are loaded
try {
    $categories = $pdo->query("SELECT * FROM job_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
    $test_results['categories'] = [
        'status' => 'success',
        'count' => count($categories),
        'message' => count($categories) . ' job categories found'
    ];
} catch (Exception $e) {
    $test_results['categories'] = [
        'status' => 'error',
        'message' => 'Error loading categories: ' . $e->getMessage()
    ];
}

// Test 2: Check if skills are loaded for a sample category
try {
    $skills = $pdo->query("
        SELECT s.*, sc.category_id 
        FROM skills s 
        JOIN skill_categories sc ON s.id = sc.skill_id 
        WHERE sc.category_id = 1 AND s.is_active = 1 
        ORDER BY s.name
    ")->fetchAll();
    $test_results['skills'] = [
        'status' => 'success',
        'count' => count($skills),
        'message' => count($skills) . ' skills found for category 1 (Agriculture)'
    ];
} catch (Exception $e) {
    $test_results['skills'] = [
        'status' => 'error',
        'message' => 'Error loading skills: ' . $e->getMessage()
    ];
}

// Test 3: Check get_skills.php endpoint
$test_results['get_skills_endpoint'] = [
    'status' => 'info',
    'message' => 'Test the AJAX endpoint manually by selecting categories in the form'
];

// Test 4: Check required tables
$required_tables = ['job_categories', 'skills', 'skill_categories', 'users', 'user_job_categories', 'user_skills'];
$missing_tables = [];

foreach ($required_tables as $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() == 0) {
            $missing_tables[] = $table;
        }
    } catch (Exception $e) {
        $missing_tables[] = $table;
    }
}

if (empty($missing_tables)) {
    $test_results['tables'] = [
        'status' => 'success',
        'message' => 'All required tables exist'
    ];
} else {
    $test_results['tables'] = [
        'status' => 'error',
        'message' => 'Missing tables: ' . implode(', ', $missing_tables)
    ];
}

// Test 5: Sample form submission test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_submit'])) {
    $test_categories = $_POST['test_categories'] ?? [];
    $test_skills = $_POST['test_skills'] ?? [];
    
    $test_results['form_submission'] = [
        'status' => 'success',
        'message' => 'Form submitted successfully. Categories: ' . count($test_categories) . ', Skills: ' . count($test_skills)
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Seeker Form Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-result { margin: 10px 0; padding: 15px; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .form-test { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .categories-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 10px 0; }
        .category-item { padding: 10px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; }
        .category-item:hover { background: #f0f9ff; }
        .skills-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 5px; margin: 10px 0; }
        .skill-item { padding: 5px; border: 1px solid #ddd; border-radius: 3px; font-size: 0.9rem; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Job Seeker Registration Form Test</h1>
        
        <h2>Test Results:</h2>
        
        <?php foreach ($test_results as $test_name => $result): ?>
            <div class="test-result <?= $result['status'] ?>">
                <strong><?= ucfirst(str_replace('_', ' ', $test_name)) ?>:</strong>
                <?= htmlspecialchars($result['message']) ?>
                <?php if (isset($result['count'])): ?>
                    <span style="font-weight: normal;"> (<?= $result['count'] ?> items)</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="form-test">
            <h3>🔧 Interactive Form Test</h3>
            <p>Test the category and skills selection functionality:</p>
            
            <form method="post">
                <h4>Select Categories:</h4>
                <div class="categories-grid">
                    <?php if (isset($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <label class="category-item">
                                <input type="checkbox" name="test_categories[]" value="<?= $category['id'] ?>" onchange="loadTestSkills()">
                                <i class="<?= htmlspecialchars($category['icon']) ?>" style="color: <?= htmlspecialchars($category['color']) ?>;"></i>
                                <?= htmlspecialchars($category['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <h4>Skills (will load based on selected categories):</h4>
                <div id="test-skills-container" class="skills-grid">
                    <p style="color: #666;">Select categories above to load skills</p>
                </div>
                
                <button type="submit" name="test_submit">Test Form Submission</button>
            </form>
        </div>
        
        <div style="margin-top: 30px;">
            <h3>🔗 Quick Links:</h3>
            <a href="signup.php?role=job_seeker" style="margin-right: 15px;">→ Actual Job Seeker Registration</a>
            <a href="debug_users.php" style="margin-right: 15px;">→ Debug Users</a>
            <a href="setup_database.php">→ Database Setup</a>
        </div>
    </div>
    
    <script>
        function loadTestSkills() {
            const selectedCategories = Array.from(document.querySelectorAll('input[name="test_categories[]"]:checked')).map(cb => cb.value);
            const skillsContainer = document.getElementById('test-skills-container');
            
            if (selectedCategories.length === 0) {
                skillsContainer.innerHTML = '<p style="color: #666;">Select categories above to load skills</p>';
                return;
            }
            
            skillsContainer.innerHTML = '<p style="color: #666;">Loading skills...</p>';
            
            fetch('get_skills.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ categories: selectedCategories })
            })
            .then(response => response.json())
            .then(skills => {
                skillsContainer.innerHTML = '';
                if (skills.error) {
                    skillsContainer.innerHTML = '<p style="color: red;">Error: ' + skills.error + '</p>';
                    return;
                }
                skills.forEach(skill => {
                    const skillLabel = document.createElement('label');
                    skillLabel.className = 'skill-item';
                    skillLabel.innerHTML = `
                        <input type="checkbox" name="test_skills[]" value="${skill.id}">
                        ${skill.name}
                    `;
                    skillsContainer.appendChild(skillLabel);
                });
            })
            .catch(error => {
                skillsContainer.innerHTML = '<p style="color: red;">Error loading skills: ' + error + '</p>';
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>
