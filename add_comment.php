<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$user_id = require_login();
$task_id = (int) ($_POST['task_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !find_owned_task($conn, $task_id, $user_id)) {
    $_SESSION['error'] = "Geen toegang tot deze taak.";
    header("Location: dashboard.php");
    exit;
}

if ($content === '') {
    $_SESSION['error'] = "Commentaar mag niet leeg zijn.";
} else {
    try {
        // Ruwe tekst opslaan; escapen gebeurt bij het tonen (item.php)
        $stmt = $conn->prepare("INSERT INTO comments (task_id, content) VALUES (?, ?)");
        $stmt->execute([$task_id, $content]);
        $_SESSION['message'] = "Commentaar toegevoegd.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Fout bij opslaan commentaar.";
    }
}

header("Location: item.php?id=" . $task_id);
exit;
