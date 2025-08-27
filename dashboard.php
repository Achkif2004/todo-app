<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// (optioneel) output buffering als extra veiligheid
// ob_start();

session_start();
require_once __DIR__ . '/includes/db.php';

// Blokkeer toegang als je niet ingelogd bent
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Haal lijsten op (pas kolommen aan naar wat jij hebt)
$stmt = $conn->prepare("SELECT id, title FROM lists WHERE user_id = ?");
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
<meta charset="UTF-8" />
<title>Dashboard</title>
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
    margin-top: 30px;
    color: #222;
}
p { text-align: center; font-weight: bold; }
form {
    display: flex;
    justify-content: center;
    margin: 20px auto;
    gap: 10px;
}
input[type="text"] {
    padding: 10px;
    border: 1px solid #bbb;
    border-radius: 6px;
    width: 240px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
input[type="text"]:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,.15);
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
    max-width: 520px;
    margin: 20px auto 40px;
}
li {
    background: #fff;
    margin: 10px 0;
    padding: 12px 15px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,.08);
}
li a {
    text-decoration: none;
    color: #333;
    font-weight: 600;
    transition: color .2s, opacity .2s;
}
li a:first-child:hover { color: #3498db; }
li a:last-child {
    color: #e74c3c;
    font-size: 18px;
    margin-left: 12px;
    opacity: .9;
}
li a:last-child:hover { opacity: 1; }
a[href="logout.php"] {
    display: block;
    text-align: center;
    margin: 30px auto;
    width: 140px;
    background: #e74c3c;
    color: #fff;
    padding: 10px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    transition: background .2s, transform .05s;
}
a[href="logout.php"]:hover { background: #c0392b; }
a[href="logout.php"]:active { transform: translateY(1px); }
@media (max-width: 1200px) {
    html { font-size: 22px; }
    body {
        min-height: 100svh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 28px;
    }
    h2 {
        font-size: 2.4rem;
        margin: 0 0 18px 0;
    }
    form {
        width: 100%;
        max-width: 760px;
        gap: 14px;
        margin: 18px auto;
        flex-direction: column;
        align-items: stretch;
        background: #fff;
        padding: 32px;
        border-radius: 16px;
        box-shadow: 0 10px 32px rgba(0,0,0,.12);
    }
    input[type="text"] {
        width: 100%;
        font-size: 1.3rem;
        padding: 18px;
        border-radius: 12px;
    }
    button {
        font-size: 1.3rem;
        padding: 18px;
        border-radius: 12px;
        min-height: 64px;
        letter-spacing: .3px;
    }
    ul {
        width: 100%;
        max-width: 760px;
        margin: 18px auto 32px;
    }
    li {
        padding: 18px 20px;
        border-radius: 14px;
    }
    li a {
        font-size: 1.15rem;
    }
    li a:last-child {
        font-size: 1.4rem;
        margin-left: 16px;
    }
    a[href="logout.php"] {
        width: 100%;
        max-width: 360px;
        font-size: 1.1rem;
        padding: 16px 18px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(231,76,60,.25);
    }
    p { font-size: 1.05rem; }
}
@media (max-width: 1200px) {
    html { font-size: 24px; }
    form { padding: 36px; border-radius: 18px; }
    input[type="text"], button { padding: 20px; border-radius: 14px; }
    h2 { font-size: 2.6rem; }
}
</style>
</head>
<body>

<h2>Welkom op je dashboard</h2>

<?php if ($flash_ok): ?>
  <p style="color:green"><?= htmlspecialchars($flash_ok) ?></p>
<?php endif; ?>
<?php if ($flash_err): ?>
  <p style="color:red"><?= htmlspecialchars($flash_err) ?></p>
<?php endif; ?>

<form action="add_list.php" method="post">
  <input type="text" name="title" placeholder="Nieuwe lijst" required>
  <button type="submit">Toevoegen</button>
</form>

<ul>
<?php if (!empty($lists)): ?>
  <?php foreach ($lists as $list): ?>
    <li>
      <a href="list.php?id=<?= (int)$list['id'] ?>"><?= htmlspecialchars($list['title']) ?></a>
      <a href="delete_list.php?id=<?= (int)$list['id'] ?>" onclick="return confirm('Weet je zeker dat je deze lijst wilt verwijderen?')">🗑️</a>
    </li>
  <?php endforeach; ?>
<?php else: ?>
  <li><em>Nog geen lijsten. Voeg je eerste lijst toe! 🎉</em></li>
<?php endif; ?>
</ul>

<a href="logout.php">Uitloggen</a>

</body>
</html>
