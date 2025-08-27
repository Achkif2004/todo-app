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
<meta charset="UTF-8">
<title><?= htmlspecialchars($list['title']) ?></title>
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
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 12px;
    max-width: 700px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,.08);
}
input[type="text"] {
    padding: 10px;
    border: 1px solid #bbb;
    border-radius: 6px;
    width: 220px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
input[type="text"]:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,.15);
}
select {
    padding: 10px;
    border: 1px solid #bbb;
    border-radius: 6px;
    background: #fafafa;
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
form p {
    width: 100%;
    text-align: center;
    margin: 10px 0;
    font-weight: normal;
}
form p a {
    color: #3498db;
    text-decoration: none;
    margin: 0 5px;
    font-weight: 600;
}
form p a:hover { text-decoration: underline; }
ul {
    list-style: none;
    padding: 0;
    max-width: 700px;
    margin: 20px auto;
}
li {
    background: #fff;
    margin: 8px 0;
    padding: 12px 15px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,.08);
}
li input[type="checkbox"] {
    margin-right: 12px;
    transform: scale(1.2);
}
li a {
    text-decoration: none;
    color: #3498db;
    font-weight: 600;
    margin-left: auto;
    transition: opacity .2s;
}
li a:hover { opacity: .8; }
a[href="dashboard.php"] {
    display: block;
    text-align: center;
    margin: 30px auto;
    width: 140px;
    background: #7f8c8d;
    color: #fff;
    padding: 10px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    transition: background .2s, transform .05s;
}
a[href="dashboard.php"]:hover { background: #636e72; }
a[href="dashboard.php"]:active { transform: translateY(1px); }
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
        gap: 16px;
        margin: 0;
        flex-direction: column;
        align-items: stretch;
        background: #fff;
        padding: 44px;
        border-radius: 22px;
        box-shadow: 0 12px 36px rgba(0,0,0,.14);
    }
    input[type="text"], select {
        width: 100%;
        font-size: 1.5rem;
        padding: 22px;
        border-radius: 14px;
    }
    button {
        font-size: 1.5rem;
        padding: 22px;
        border-radius: 14px;
        min-height: 72px;
        letter-spacing: .4px;
    }
    form p, form p a { font-size: 1.2rem; }
    ul {
        width: 100%;
        max-width: 820px;
        margin: 0;
    }
    li {
        padding: 22px 24px;
        border-radius: 18px;
        font-size: 1.2rem;
    }
    li input[type="checkbox"] {
        transform: scale(1.6);
        margin-right: 16px;
    }
    a[href="dashboard.php"] {
        width: 100%;
        max-width: 420px;
        font-size: 1.3rem;
        padding: 20px 22px;
        border-radius: 14px;
        box-shadow: 0 8px 26px rgba(127,140,141,.25);
    }
}
@media (max-width: 380px) {
    html { font-size: 28px; }
    form { padding: 48px; border-radius: 24px; }
    input[type="text"], select, button { padding: 24px; border-radius: 16px; }
    h2 { font-size: 3rem; }
}
</style>
</head>
<body>

<h2><?= htmlspecialchars($list['title']) ?></h2>

<?php if (isset($_GET['success']) && $_GET['success'] === 'task'): ?>
    <p style="color: green;">Taak toegevoegd!</p>
<?php endif; ?>

<form action="add_task.php" method="post">
    <input type="hidden" name="list_id" value="<?= (int)$list_id ?>">
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
