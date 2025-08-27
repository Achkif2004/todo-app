<?php
session_start();
require_once __DIR__ . '/includes/db.php';


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

// Optioneel: bestandnaam opschonen (veilig)
$filename = preg_replace('/[^\w\-. ]+/', '_', $filename);

$uploadsDir = __DIR__ . '/uploads/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0775, true);
}

$target = $uploadsDir . $filename;

// Optioneel: naamconflict opvangen
if (file_exists($target)) {
    $pathinfo = pathinfo($filename);
    $base = $pathinfo['filename'];
    $ext  = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
    $i = 2;
    while (file_exists($uploadsDir . $base . " ($i)" . $ext)) $i++;
    $filename = $base . " ($i)" . $ext;
    $target   = $uploadsDir . $filename;
}

if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
    $stmt = $conn->prepare("INSERT INTO files (task_id, filename) VALUES (?, ?)");
    $stmt->execute([$task_id, $filename]);
    $_SESSION['message'] = "Bestand geüpload.";
} else {
    $_SESSION['error'] = "Upload mislukt.";
}

header("Location: item.php?id=" . $task_id);