<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>


<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
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
