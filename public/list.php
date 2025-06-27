<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$list_id = $_GET['id'];
$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT * FROM lists WHERE id = ? AND user_id = ?");
$stmt->execute([$list_id, $user_id]);
$list = $stmt->fetch();
if (!$list) {
    die("Lijst niet gevonden.");
}


$stmt = $conn->prepare("SELECT * FROM tasks WHERE list_id = ? ORDER BY 
  FIELD(priority, 'high', 'medium', 'low'), id DESC");
$stmt->execute([$list_id]);
$tasks = $stmt->fetchAll();
?>

<h2><?= htmlspecialchars($list['title']) ?></h2>

<form action="add_task.php" method="post">
    <input type="hidden" name="list_id" value="<?= $list_id ?>">
    <input type="text" name="title" placeholder="Nieuwe taak" required>
    <select name="priority">
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
    </select>
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

