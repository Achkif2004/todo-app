<?php
session_start();
require_once '../includes/db.php';

$task_id = $_POST['task_id'] ?? null;
$content = $_POST['content'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if ($task_id && $content && $user_id) {
    try {
        $stmt = $conn->prepare("INSERT INTO comments (task_id, content) VALUES (?, ?)");
        $stmt->execute([$task_id, htmlspecialchars($content)]);
        $_SESSION['message'] = "Commentaar toegevoegd.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Fout bij opslaan commentaar.";
    }
}
header("Location: item.php?id=" . $task_id);
