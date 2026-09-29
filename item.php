<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/db.php';

// --- Veilig id + sessie check ---
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'] ?? null;

if ($task_id <= 0 || !$user_id) {
    die("Geen toegang");
}

// --- Taak + eigendom controleren ---
$stmt = $conn->prepare("
    SELECT t.*, l.user_id 
    FROM tasks t 
    JOIN lists l ON t.list_id = l.id 
    WHERE t.id = ? AND l.user_id = ?
");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    die("Taak niet gevonden of geen toegang");
}

// --- Comments & files ophalen ---
$stmt = $conn->prepare("SELECT id, content, created_at FROM comments WHERE task_id = ? ORDER BY created_at DESC");
$stmt->execute([$task_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT id, filename FROM files WHERE task_id = ?");
$stmt->execute([$task_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash messages
$flash_ok  = $_SESSION['message'] ?? null;
$flash_err = $_SESSION['error'] ?? null;
unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<?php $pageTitle = $task['title']; require __DIR__ . '/includes/head.php'; ?>
</head>
<body>
<?php $prioLabels = ['low' => 'Laag', 'medium' => 'Gemiddeld', 'high' => 'Hoog']; ?>

<?php require __DIR__ . '/includes/nav.php'; ?>

<main class="page">
  <div class="container">

    <a class="back-link" href="list.php?id=<?= (int)$task['list_id'] ?>">
      <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      Terug naar lijst
    </a>

    <?php if ($flash_ok): ?>
      <div class="alert alert-success"><?= htmlspecialchars($flash_ok) ?></div>
    <?php endif; ?>
    <?php if ($flash_err): ?>
      <div class="alert alert-error"><?= htmlspecialchars($flash_err) ?></div>
    <?php endif; ?>

    <div class="page-header">
      <h1><?= htmlspecialchars($task['title']) ?></h1>
      <div class="meta-row">
        <span class="badge badge-<?= htmlspecialchars($task['priority']) ?>">Prioriteit: <?= $prioLabels[$task['priority']] ?? htmlspecialchars($task['priority']) ?></span>
        <?php if (!empty($task['done'])): ?>
          <span class="badge badge-done">Afgerond</span>
        <?php else: ?>
          <span class="badge badge-open">Nog te doen</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="detail-grid">

      <section class="card">
        <h2 class="section-title">Commentaar <span class="count"><?= count($comments) ?></span></h2>

        <form action="add_comment.php" method="post">
          <input type="hidden" name="task_id" value="<?= (int)$task_id ?>">
          <textarea name="content" required placeholder="Typ je commentaar..." aria-label="Commentaar"></textarea>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Voeg commentaar toe</button>
          </div>
        </form>

        <?php if (!empty($comments)): ?>
        <ul class="comment-list">
        <?php foreach ($comments as $c): ?>
          <li class="comment">
            <?= htmlspecialchars($c['content']) ?>
            <em class="comment-date"><?= htmlspecialchars($c['created_at']) ?></em>
          </li>
        <?php endforeach; ?>
        </ul>
        <?php else: ?>
          <p class="empty-inline">Nog geen commentaar bij deze taak.</p>
        <?php endif; ?>
      </section>

      <section class="card">
        <h2 class="section-title">Bijlagen <span class="count"><?= count($files) ?></span></h2>

        <form action="upload_file.php" method="post" enctype="multipart/form-data" class="upload-form">
          <input type="hidden" name="task_id" value="<?= (int)$task_id ?>">
          <input type="file" name="file" required aria-label="Bestand kiezen">
          <button type="submit" class="btn btn-ghost btn-block">Upload bestand</button>
        </form>

        <?php if (!empty($files)): ?>
        <ul class="file-list">
        <?php foreach ($files as $f): ?>
          <li>
            <!-- Als /www/uploads/ bestaat, geen .. gebruiken -->
            <a href="uploads/<?= rawurlencode(basename($f['filename'])) ?>" target="_blank">
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
              <span class="file-name"><?= htmlspecialchars($f['filename']) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
        </ul>
        <?php else: ?>
          <p class="empty-inline">Nog geen bestanden geüpload.</p>
        <?php endif; ?>
      </section>

    </div>

  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
