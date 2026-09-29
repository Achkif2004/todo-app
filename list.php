<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$list_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$list_id) {
    die("Geen lijst opgegeven.");
}

$sortType = $_GET['type'] ?? 'priority';
$sortOrder = $_GET['sort'] ?? 'asc';

$allowedTypes = ['title', 'priority'];
$allowedOrders = ['asc', 'desc'];

if (!in_array($sortType, $allowedTypes)) $sortType = 'priority';
if (!in_array($sortOrder, $allowedOrders)) $sortOrder = 'asc';

$orderBy = $sortType === 'priority'
    ? "FIELD(priority, 'high', 'medium', 'low')" . ($sortOrder === 'desc' ? ' DESC' : '')
    : "$sortType " . strtoupper($sortOrder);

$stmt = $conn->prepare("SELECT * FROM tasks WHERE list_id = ? ORDER BY $orderBy");
$stmt->execute([$list_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM lists WHERE id = ? AND user_id = ?");
$stmt->execute([$list_id, $user_id]);
$list = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$list) {
    die("Lijst niet gevonden.");
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<?php $pageTitle = $list['title']; require __DIR__ . '/includes/head.php'; ?>
</head>
<body>
<?php
// Weergave-hulpjes (enkel voor de opmaak)
$prioLabels = ['low' => 'Laag', 'medium' => 'Gemiddeld', 'high' => 'Hoog'];
$sortOptions = [
    ['priority', 'asc',  'Hoogste prioriteit'],
    ['priority', 'desc', 'Laagste prioriteit'],
    ['title',    'asc',  'Titel A–Z'],
    ['title',    'desc', 'Titel Z–A'],
];
?>

<?php require __DIR__ . '/includes/nav.php'; ?>

<main class="page">
  <div class="container container-narrow">

    <a class="back-link" href="dashboard.php">
      <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      Alle lijsten
    </a>

    <div class="page-header">
      <h1><?= htmlspecialchars($list['title']) ?></h1>
      <p class="subtitle">Voeg taken toe, geef ze een prioriteit en vink af wat klaar is.</p>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'task'): ?>
      <div class="alert alert-success">Taak toegevoegd!</div>
    <?php endif; ?>

    <form action="add_task.php" method="post" class="card task-form">
      <input type="hidden" name="list_id" value="<?= (int)$list_id ?>">
      <input type="text" name="title" placeholder="Nieuwe taak, bv. Hotel boeken" aria-label="Nieuwe taak" required>
      <select name="priority" aria-label="Prioriteit">
        <option value="low">Prioriteit: laag</option>
        <option value="medium">Prioriteit: gemiddeld</option>
        <option value="high">Prioriteit: hoog</option>
      </select>
      <button type="submit" class="btn btn-primary">Toevoegen</button>
    </form>

    <div class="toolbar">
      <h2 class="section-title">Taken <span class="count"><?= count($tasks) ?></span></h2>
      <nav class="sort" aria-label="Sorteren">
        <span class="sort-label">Sorteren:</span>
        <?php foreach ($sortOptions as [$type, $order, $label]): ?>
          <a class="chip<?= ($sortType === $type && $sortOrder === $order) ? ' is-active' : '' ?>"
             href="?id=<?= (int)$list_id ?>&amp;type=<?= $type ?>&amp;sort=<?= $order ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </nav>
    </div>

    <?php if (!empty($tasks)): ?>
    <ul class="task-list">
    <?php foreach ($tasks as $task): ?>
      <li class="task">
        <input type="checkbox" class="done-toggle"
               id="task-<?= (int)$task['id'] ?>"
               data-id="<?= (int)$task['id'] ?>"
               <?= $task['done'] ? 'checked' : '' ?>>
        <label class="task-title" for="task-<?= (int)$task['id'] ?>"><?= htmlspecialchars($task['title']) ?></label>
        <span class="badge badge-<?= htmlspecialchars($task['priority']) ?>"><?= $prioLabels[$task['priority']] ?? htmlspecialchars($task['priority']) ?></span>
        <a class="task-link" href="item.php?id=<?= (int)$task['id'] ?>">Details →</a>
      </li>
    <?php endforeach; ?>
    </ul>
    <?php else: ?>
      <div class="empty">
        <strong>Nog geen taken</strong>
        Voeg hierboven je eerste taak toe.
      </div>
    <?php endif; ?>

  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
document.querySelectorAll('.done-toggle').forEach(box => {
    box.addEventListener('change', () => {
        fetch('toggle_done.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'task_id=' + box.dataset.id + '&done=' + (box.checked ? 1 : 0)
        })
        .then(res => res.text())
        .then(data => console.log(data))
        .catch(err => alert('Fout bij updaten taak'));
    });
});

// Reload pagina als gebruiker via back-button terugkomt
window.addEventListener("pageshow", function (event) {
    if (event.persisted) window.location.reload();
});

// Verberg ?success=task in URL
if (window.location.search.includes('success=task')) {
    const url = new URL(window.location);
    url.searchParams.delete('success');
    window.history.replaceState({}, document.title, url.pathname + url.search);
}
</script>

</body>
</html>
