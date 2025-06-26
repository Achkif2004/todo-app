<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $done = $_POST['done'] ?? 0;

    if (!$task_id || !is_numeric($done)) {
        http_response_code(400);
        echo "Ongeldige input";
        exit;
    }

    // Veiligheidscheck: taak moet toebehoren aan gebruiker
    $stmt = $conn->prepare("
        SELECT t.*, l.user_id
        FROM tasks t
        JOIN lists l ON t.list_id = l.id
        WHERE t.id = ? AND l.user_id = ?
    ");
    $stmt->execute([$task_id, $_SESSION['user_id']]);
    $task = $stmt->fetch();

    if (!$task) {
        http_response_code(403);
        echo "Geen toegang tot deze taak";
        exit;
    }

    // Update status
    $stmt = $conn->prepare("UPDATE tasks SET done = ? WHERE id = ?");
    $stmt->execute([$done, $task_id]);
    echo "Status aangepast";
}
?>
