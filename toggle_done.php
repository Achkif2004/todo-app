<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Enkel POST toegestaan";
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Niet ingelogd";
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$task_id = (int) ($_POST['task_id'] ?? 0);
$done = !empty($_POST['done']) ? 1 : 0;

if ($task_id <= 0) {
    http_response_code(400);
    echo "Ongeldige input";
    exit;
}

if (!find_owned_task($conn, $task_id, $user_id)) {
    http_response_code(403);
    echo "Geen toegang tot deze taak";
    exit;
}

$stmt = $conn->prepare("UPDATE tasks SET done = ? WHERE id = ?");
$stmt->execute([$done, $task_id]);
echo "Status aangepast";
