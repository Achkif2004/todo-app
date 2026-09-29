<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$user_id = require_login();
$list_id = (int) ($_POST['list_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title'] ?? '');
        $priority = $_POST['priority'] ?? '';

        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            throw new Exception("Ongeldige prioriteit.");
        }

        if ($title === '') {
            throw new Exception("Titel mag niet leeg zijn.");
        }

        $stmt = $conn->prepare("SELECT id FROM lists WHERE id = ? AND user_id = ?");
        $stmt->execute([$list_id, $user_id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "Lijst niet gevonden.";
            header("Location: dashboard.php");
            exit;
        }

        $stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE list_id = ? AND title = ?");
        $stmt->execute([$list_id, $title]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Deze taak bestaat al in deze lijst.");
        }

        // Ruwe tekst opslaan; escapen gebeurt bij het tonen
        $stmt = $conn->prepare("INSERT INTO tasks (list_id, title, priority) VALUES (?, ?, ?)");
        $stmt->execute([$list_id, $title, $priority]);

        $_SESSION['message'] = "Taak toegevoegd!";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: list.php?id=" . $list_id);
exit;
