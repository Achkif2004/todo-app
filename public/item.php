<?php
session_start();
require_once '../includes/db.php';

$task_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$task_id || !$user_id) {
    die("Geen toegang");
}

$stmt = $conn->prepare("
    SELECT t.*, l.user_id 
    FROM tasks t 
    JOIN lists l ON t.list_id = l.id 
    WHERE t.id = ? AND l.user_id = ?
");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) die("Taak niet gevonden of geen toegang");

$stmt = $conn->prepare("SELECT * FROM comments WHERE task_id = ? ORDER BY created_at DESC");
$stmt->execute([$task_id]);
$comments = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT * FROM files WHERE task_id = ?");
$stmt->execute([$task_id]);
$files = $stmt->fetchAll();
?>

<?php
if (isset($_SESSION['message'])) {
    echo "<p style='color:green'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo "<p style='color:red'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}
?>

<h2><?= htmlspecialchars($task['title']) ?> (<?= $task['priority'] ?>)</h2>

<form action="add_comment.php" method="post">
    <input type="hidden" name="task_id" value="<?= $task_id ?>">
    <textarea name="content" required placeholder="Typ je commentaar..."></textarea>
    <button type="submit">Voeg commentaar toe</button>
</form>

<ul>
<?php foreach ($comments as $c): ?>
    <li><?= htmlspecialchars($c['content']) ?> <em><?= $c['created_at'] ?></em></li>
<?php endforeach; ?>
</ul>

<form action="upload_file.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="task_id" value="<?= $task_id ?>">
    <input type="file" name="file" required>
    <button type="submit">Upload bestand</button>
</form>

<ul>
<?php foreach ($files as $f): ?>
    <li><a href="../uploads/<?= htmlspecialchars($f['filename']) ?>" target="_blank"><?= htmlspecialchars($f['filename']) ?></a></li>
<?php endforeach; ?>
</ul>

<a href="list.php?id=<?= $task['list_id'] ?>">← Terug naar lijst</a>
