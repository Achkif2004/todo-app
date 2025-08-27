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
p {
    text-align: center;
    font-weight: bold;
}
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
@media (max-width: 560px) {
    form { flex-direction: column; align-items: center; }
    input[type="text"] { width: 90%; }
    ul { max-width: 90%; }
}
</style>
<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_SESSION['message'])) {
    echo "<p style='color:green'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo "<p style='color:red'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM lists WHERE user_id = ?");
$stmt->execute([$user_id]);
$lists = $stmt->fetchAll();
?>

<h2>Welkom op je dashboard</h2>

<form action="add_list.php" method="post">
    <input type="text" name="title" placeholder="Nieuwe lijst" required>
    <button type="submit">Toevoegen</button>
</form>

<ul>
    <?php foreach ($lists as $list): ?>
        <li>
            <a href="list.php?id=<?= $list['id'] ?>"><?= htmlspecialchars($list['title']) ?></a>
            <a href="delete_list.php?id=<?= $list['id'] ?>" onclick="return confirm('Weet je zeker dat je deze lijst wilt verwijderen?')">🗑️</a>
        </li>
    <?php endforeach; ?>
</ul>

<a href="logout.php">Uitloggen</a>

