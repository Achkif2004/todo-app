<?php
session_start();
require_once '../includes/db.php';

$task_id = $_POST['task_id'];
$user_id = $_SESSION['user_id'] ?? null;

if (!$task_id || !$user_id || !isset($_FILES['file'])) {
    $_SESSION['error'] = "Ongeldig verzoek.";
    header("Location: item.php?id=" . $task_id);
    exit;
}

$stmt = $conn->prepare("SELECT t.*, l.user_id FROM tasks t JOIN lists l ON t.list_id = l.id WHERE t.id = ? AND l.user_id = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();
if (!$task) {
    $_SESSION['error'] = "Geen toegang tot taak.";
    header("Location: item.php?id=" . $task_id);
    exit;
}

$filename = basename($_FILES['file']['name']);
$target = "../uploads/" . $filename;

if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
    $stmt = $conn->prepare("INSERT INTO files (task_id, filename) VALUES (?, ?)");
    $stmt->execute([$task_id, $filename]);
    $_SESSION['message'] = "Bestand geüpload.";
} else {
    $_SESSION['error'] = "Upload mislukt.";
}

header("Location: item.php?id=" . $task_id);
