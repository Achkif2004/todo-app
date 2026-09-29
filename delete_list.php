<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$user_id = require_login();

// Verwijderen enkel via POST (niet via een gewone link)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$list_id = (int) ($_POST['id'] ?? 0);

// Controleer dat de lijst van deze gebruiker is
$stmt = $conn->prepare("SELECT id FROM lists WHERE id = ? AND user_id = ?");
$stmt->execute([$list_id, $user_id]);

if (!$stmt->fetch()) {
    $_SESSION['error'] = "Lijst niet gevonden.";
    header("Location: dashboard.php");
    exit;
}

try {
    $conn->beginTransaction();

    // Geüploade bestanden van deze lijst opzoeken (om ze ook van de server te wissen)
    $stmt = $conn->prepare("
        SELECT f.filename FROM files f
        JOIN tasks t ON f.task_id = t.id
        WHERE t.list_id = ?
    ");
    $stmt->execute([$list_id]);
    $filenames = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Eerst alles wat aan de taken hangt, dan de taken, dan de lijst
    $conn->prepare("DELETE FROM comments WHERE task_id IN (SELECT id FROM tasks WHERE list_id = ?)")->execute([$list_id]);
    $conn->prepare("DELETE FROM files WHERE task_id IN (SELECT id FROM tasks WHERE list_id = ?)")->execute([$list_id]);
    $conn->prepare("DELETE FROM tasks WHERE list_id = ?")->execute([$list_id]);
    $conn->prepare("DELETE FROM lists WHERE id = ? AND user_id = ?")->execute([$list_id, $user_id]);

    $conn->commit();

    foreach ($filenames as $name) {
        $path = __DIR__ . '/uploads/' . basename($name);
        if (is_file($path)) {
            unlink($path);
        }
    }

    $_SESSION['message'] = "Lijst verwijderd.";
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Verwijderen mislukt.";
}

header("Location: dashboard.php");
exit;
