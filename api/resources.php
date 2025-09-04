<?php
require_once __DIR__ . '/inc/db.php';
$templates = $pdo->query("SELECT * FROM contract_templates")->fetchAll();
$faqs = $pdo->query("SELECT * FROM faqs")->fetchAll();
$resources = $pdo->query("SELECT * FROM resources")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Resources – Sconnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: 'Inter', Arial, sans-serif; background: #f9fafb; margin:0; }
    .container { max-width: 700px; margin: 2rem auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 2rem 1.5rem; }
    h2 { color: #4f46e5; }
    .section { margin-bottom: 2rem; }
    .resource-list { list-style: none; padding: 0; }
    .resource-list li { margin-bottom: 1rem; }
    .faq { background: #eef2ff; border-radius: 10px; padding: 1rem; margin-bottom: 1rem; }
    @media (max-width: 700px) { .container { margin: 0.5rem; padding: 1rem 0.5rem; } }
  </style>
</head>
<body>
  <div class="container">
    <h2>Resources</h2>
    <div class="section">
      <h3>Educational Videos & Articles</h3>
      <ul class="resource-list">
        <?php foreach ($resources as $res): ?>
          <li><a href="<?= htmlspecialchars($res['url']) ?>"><?= htmlspecialchars($res['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="section">
      <h3>Downloadable Templates</h3>
      <ul class="resource-list">
        <?php foreach ($templates as $tpl): ?>
          <li><a href="<?= htmlspecialchars($tpl['file_path']) ?>"><?= htmlspecialchars($tpl['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="section">
      <h3>FAQs</h3>
      <?php foreach ($faqs as $faq): ?>
        <div class="faq"><b><?= htmlspecialchars($faq['question']) ?></b><br><?= htmlspecialchars($faq['answer']) ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>