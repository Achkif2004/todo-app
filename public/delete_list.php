<?php
session_start();
require_once '../includes/db.php';

$list_id = $_GET['id'] ?? null;

if ($list_id) {
    // Verwijder eerst alle taken die aan deze lijst verbonden zijn
    $stmt = $conn->prepare("DELETE FROM tasks WHERE list_id = ?");
    $stmt->execute([$list_id]);

    // Verwijder dan pas de lijst zelf
    $stmt = $conn->prepare("DELETE FROM lists WHERE id = ?");
    $stmt->execute([$list_id]);

    $_SESSION['message'] = "Lijst verwijderd.";
}

header("Location: dashboard.php");
exit;
