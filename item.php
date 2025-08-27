<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// --- Veilig id ophalen ---
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
$task = $stmt->fetch();

if (!$task) {
    die("Taak niet gevonden of geen toegang");
}

// --- Comments & files ophalen ---
$stmt = $conn->prepare("SELECT * FROM comments WHERE task_id = ? ORDER BY created_at DESC");
$stmt->execute([$task_id]);
$comments = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT * FROM files WHERE task_id = ?");
$stmt->execute([$task_id]);
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Taakdetails</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0;
    color: #333;
}
h2 {
    text-align: center;
    margin: 30px auto 20px;
    color: #222;
}
p {
    text-align: center;
    font-weight: bold;
}
form {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    max-width: 500px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,.08);
}
textarea {
    width: 100%;
    min-height: 80px;
    padding: 10px;
    border: 1px solid #bbb;
    border-radius: 6px;
    resize: vertical;
    font-family: inherit;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
textarea:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,.15);
}
input[type="file"] {
    border: 1px solid #bbb;
    padding: 6px;
    border-radius: 6px;
    background: #fafafa;
    width: 100%;
}
button {
    background: #3498db;
    border: none;
    color: #fff;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background .2s, transform .05s;
}
button:hover { background: #2980b9; }
button:active { transform: translateY(1px); }
ul {
    list-style: none;
    padding: 0;
    max-width: 600px;
    margin: 20px auto;
}
li {
    background: #fff;
    margin: 8px 0;
    padding: 12px 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,.08);
}
li em {
    display: block;
    font-size: 12px;
    color: #666;
    margin-top: 6px;
}
li a {
    text-decoration: none;
    color: #3498db;
    font-weight: 600;
}
li a:hover {
    text-decoration: underline;
}
a[href^="list.php"] {
    display: block;
    text-align: center;
    margin: 30px auto;
    width: 180px;
    background: #7f8c8d;
    color: #fff;
    padding: 10px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    transition: background .2s, transform .05s;
}
a[href^="list.php"]:hover { background: #636e72; }
a[href^="list.php"]:active { transform: translateY(1px); }
@media (max-width: 560px) {
    form { width: 90%; }
    ul { max-width: 90%; }
}
</style>
</head>
<body>

<?php
session_start();
require_once __DIR__ . '/includes/db.php';


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
    <li><a href="uploads/<?= rawurlencode($f['filename']) ?>" target="_blank">
    <?= htmlspecialchars($f['filename']) ?>
</a></li>

<?php endforeach; ?>
</ul>

<a href="list.php?id=<?= $task['list_id'] ?>">← Terug naar lijst</a>
</body>
</html>
