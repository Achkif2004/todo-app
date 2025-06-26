<?php
session_start();
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM lists WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}
header("Location: dashboard.php");
?>
