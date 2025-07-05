<?php

session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$list_id = $_GET['id'];
$user_id = $_SESSION['user_id'];


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
$tasks = $stmt->fetchAll();



$stmt = $conn->prepare("SELECT * FROM lists WHERE id = ? AND user_id = ?");
$stmt->execute([$list_id, $user_id]);
$list = $stmt->fetch();
if (!$list) {
    die("Lijst niet gevonden.");
}



?>

<h2><?= htmlspecialchars($list['title']) ?></h2>
<?php if (isset($_GET['success']) && $_GET['success'] == 'task'): ?>
    <p style="color: green;">Taak toegevoegd!</p>
<?php endif; ?>


<form action="add_task.php" method="post">
    <input type="hidden" name="list_id" value="<?= $list_id ?>">
    <input type="text" name="title" placeholder="Nieuwe taak" required>
    <select name="priority">
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
    </select>
    <p>Sorteren op:
        <a href="?id=<?= $list_id ?>&type=title&sort=asc">Titel ↑</a> |
        <a href="?id=<?= $list_id ?>&type=title&sort=desc">Titel ↓</a> |
        <a href="?id=<?= $list_id ?>&type=priority&sort=asc">Prioriteit ↑</a> |
        <a href="?id=<?= $list_id ?>&type=priority&sort=desc">Prioriteit ↓</a>
    </p>

    <button type="submit">Toevoegen</button>
</form>

<ul>
<?php foreach ($tasks as $task): ?>
    <li>
        <input type="checkbox" class="done-toggle"
                data-id="<?= $task['id'] ?>"
                <?= $task['done'] ? 'checked' : '' ?>>
        <?= htmlspecialchars($task['title']) ?> (<?= $task['priority'] ?>)
        <a href="item.php?id=<?= $task['id'] ?>">Details</a>
    </li>

<?php endforeach; ?>
</ul>

<a href="dashboard.php">← Terug</a>


<script>
document.querySelectorAll('.done-toggle').forEach(box => {
    box.addEventListener('change', () => {
        fetch('toggle_done.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'task_id=' + box.dataset.id + '&done=' + (box.checked ? 1 : 0)
        })
        .then(res => res.text())
        .then(data => console.log(data))
        .catch(err => alert('Fout bij updaten taak'));
    });
});
</script>

