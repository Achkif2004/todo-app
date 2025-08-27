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
p { text-align: center; font-weight: bold; }
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
li a:hover { text-decoration: underline; }
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
@media (max-width: 1200px) {
    html { font-size: 26px; }
    body {
        min-height: 100svh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 28px;
        gap: 18px;
    }
    h2 {
        font-size: 2.8rem;
        margin: 0;
        text-align: center;
    }
    form {
        width: 100%;
        max-width: 820px;
        padding: 44px;
        border-radius: 22px;
        box-shadow: 0 12px 36px rgba(0,0,0,.14);
        gap: 16px;
    }
    textarea {
        font-size: 1.5rem;
        padding: 22px;
        border-radius: 14px;
        min-height: 140px;
    }
    input[type="file"] {
        font-size: 1.2rem;
        padding: 16px;
        border-radius: 14px;
    }
    button {
        font-size: 1.5rem;
        padding: 22px;
        border-radius: 14px;
        min-height: 72px;
        letter-spacing: .4px;
    }
    ul {
        width: 100%;
        max-width: 820px;
        margin: 0;
    }
    li {
        padding: 22px 24px;
        border-radius: 18px;
        font-size: 1.15rem;
    }
    li em {
        font-size: .9rem;
        margin-top: 8px;
    }
    a[href^="list.php"] {
        width: 100%;
        max-width: 420px;
        font-size: 1.3rem;
        padding: 20px 22px;
        border-radius: 14px;
        box-shadow: 0 8px 26px rgba(127,140,141,.25);
    }
    p { font-size: 1.2rem; }
}

@media (max-width: 1200px) {
    html { font-size: 28px; }
    form { padding: 48px; border-radius: 24px; }
    textarea, input[type="file"], button { padding: 24px; border-radius: 16px; }
    h2 { font-size: 3rem; }
}
</style>
</head>
<body>

<?php if ($flash_ok): ?>
  <p style="color:green"><?= htmlspecialchars($flash_ok) ?></p>
<?php endif; ?>
<?php if ($flash_err): ?>
  <p style="color:red"><?= htmlspecialchars($flash_err) ?></p>
<?php endif; ?>

<h2><?= htmlspecialchars($task['title']) ?> (<?= htmlspecialchars($task['priority']) ?>)</h2>

<form action="add_comment.php" method="post">
    <input type="hidden" name="task_id" value="<?= (int)$task_id ?>">
    <textarea name="content" required placeholder="Typ je commentaar..."></textarea>
    <button type="submit">Voeg commentaar toe</button>
</form>

<ul>
<?php foreach ($comments as $c): ?>
  <li>
    <?= htmlspecialchars($c['content']) ?>
    <em><?= htmlspecialchars($c['created_at']) ?></em>
  </li>
<?php endforeach; ?>
</ul>

<form action="upload_file.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="task_id" value="<?= (int)$task_id ?>">
    <input type="file" name="file" required>
    <button type="submit">Upload bestand</button>
</form>

<ul>
<?php foreach ($files as $f): ?>
  <li>
    <!-- Als /www/uploads/ bestaat, geen .. gebruiken -->
    <a href="uploads/<?= rawurlencode(basename($f['filename'])) ?>" target="_blank">
      <?= htmlspecialchars($f['filename']) ?>
    </a>
  </li>
<?php endforeach; ?>
</ul>

<a href="list.php?id=<?= (int)$task['list_id'] ?>">← Terug naar lijst</a>

</body>
</html>
