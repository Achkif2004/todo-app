<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Blokkeer toegang als je niet ingelogd bent
$user_id = require_login();

// Haal lijsten op
$stmt = $conn->prepare("SELECT id, title FROM lists WHERE user_id = ? ORDER BY id");
$stmt->execute([$user_id]);
// Zorg dat we associatieve arrays krijgen
$lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash messages ophalen (en meteen weghalen)
$flash_ok = $_SESSION['message'] ?? null;
$flash_err = $_SESSION['error'] ?? null;
unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<?php $pageTitle = 'Dashboard'; require __DIR__ . '/includes/head.php'; ?>
</head>
<body>

<?php require __DIR__ . '/includes/nav.php'; ?>

<main class="page">
  <div class="container">

    <div class="page-header">
      <h1>Mijn reislijsten</h1>
      <p class="subtitle">Maak een lijst per reis en hou bij wat er nog moet gebeuren.</p>
    </div>

    <?php if ($flash_ok): ?>
      <div class="alert alert-success"><?= htmlspecialchars($flash_ok) ?></div>
    <?php endif; ?>
    <?php if ($flash_err): ?>
      <div class="alert alert-error"><?= htmlspecialchars($flash_err) ?></div>
    <?php endif; ?>

    <form action="add_list.php" method="post" class="card form-row">
      <input type="text" name="title" placeholder="Nieuwe lijst, bv. Roadtrip Italië" aria-label="Naam van de nieuwe lijst" required>
      <button type="submit" class="btn btn-primary">Lijst toevoegen</button>
    </form>

    <?php if (!empty($lists)): ?>
    <ul class="list-grid">
      <?php foreach ($lists as $list): ?>
        <li class="list-card">
          <span class="list-card-icon">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          </span>
          <div class="list-card-body">
            <a class="list-card-link" href="list.php?id=<?= (int)$list['id'] ?>"><?= htmlspecialchars($list['title']) ?></a>
            <span class="list-card-meta">Bekijk taken →</span>
          </div>
          <form action="delete_list.php" method="post" class="delete-form" onsubmit="return confirm('Weet je zeker dat je deze lijst wilt verwijderen?')">
            <input type="hidden" name="id" value="<?= (int)$list['id'] ?>">
            <button type="submit" class="icon-btn" title="Lijst verwijderen" aria-label="Lijst verwijderen">
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
            </button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?>
      <div class="empty">
        <strong>Nog geen lijsten</strong>
        Voeg hierboven je eerste reislijst toe. 🎉
      </div>
    <?php endif; ?>

  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
