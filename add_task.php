<?php
session_start();
require_once __DIR__ . '/includes/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $list_id = $_POST['list_id'];
        $title = htmlspecialchars($_POST['title']);
        $priority = $_POST['priority'];
        $user_id = $_SESSION['user_id'];

        if (!in_array($priority, ['low', 'medium', 'high'])) {
            throw new Exception("Ongeldige prioriteit.");
        }

        if (empty($title)) {
            throw new Exception("Titel mag niet leeg zijn.");
        }

        $stmt = $conn->prepare("SELECT * FROM lists WHERE id = ? AND user_id = ?");
        $stmt->execute([$list_id, $user_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Lijst niet gevonden.");
        }

        $stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE list_id = ? AND title = ?");
        $stmt->execute([$list_id, $title]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Deze taak bestaat al in deze lijst.");
        }

        $stmt = $conn->prepare("INSERT INTO tasks (list_id, title, priority) VALUES (?, ?, ?)");
        $stmt->execute([$list_id, $title, $priority]);

        $_SESSION['message'] = "Taak toegevoegd!";
        header("Location: list.php?id=" . $list_id . "&success=task");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: list.php?id=" . $list_id . "&success=task");
        exit;
    }
}