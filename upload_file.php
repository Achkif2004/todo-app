<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$user_id = require_login();
$task_id = (int) ($_POST['task_id'] ?? 0);

// Enkel deze bestandstypes zijn toegestaan (extensie => toegelaten MIME-types)
$allowed = [
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'gif'  => ['image/gif'],
    'webp' => ['image/webp'],
    'pdf'  => ['application/pdf'],
    'txt'  => ['text/plain'],
];
$maxSize = 5 * 1024 * 1024; // 5 MB

function fail(string $message, int $task_id): void
{
    $_SESSION['error'] = $message;
    header("Location: item.php?id=" . $task_id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $task_id <= 0 || !isset($_FILES['file'])) {
    fail("Ongeldig verzoek.", $task_id);
}

if (!find_owned_task($conn, $task_id, $user_id)) {
    fail("Geen toegang tot taak.", $task_id);
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    fail("Upload mislukt.", $task_id);
}

if ($file['size'] > $maxSize) {
    fail("Bestand is te groot (max. 5 MB).", $task_id);
}

// Bestandsnaam opschonen en extensie controleren
$filename = preg_replace('/[^\w\-. ]+/', '_', basename($file['name']));
$pathinfo = pathinfo($filename);
$ext = strtolower($pathinfo['extension'] ?? '');

if (!isset($allowed[$ext])) {
    fail("Dit bestandstype is niet toegestaan. Toegelaten: " . implode(', ', array_keys($allowed)) . ".", $task_id);
}

// Echte inhoud controleren, niet enkel de extensie
if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed[$ext], true)) {
        fail("De inhoud van het bestand komt niet overeen met de extensie.", $task_id);
    }
}

$uploadsDir = __DIR__ . '/uploads/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Veilige naam: zonder extra punten, met de gecontroleerde extensie
$base = trim(str_replace('.', '_', $pathinfo['filename'])) ?: 'bestand';
$filename = $base . '.' . $ext;
$target = $uploadsDir . $filename;

// Naamconflict opvangen
$i = 2;
while (file_exists($target)) {
    $filename = $base . " ($i)." . $ext;
    $target = $uploadsDir . $filename;
    $i++;
}

if (move_uploaded_file($file['tmp_name'], $target)) {
    $stmt = $conn->prepare("INSERT INTO files (task_id, filename) VALUES (?, ?)");
    $stmt->execute([$task_id, $filename]);
    $_SESSION['message'] = "Bestand geüpload.";
} else {
    $_SESSION['error'] = "Upload mislukt.";
}

header("Location: item.php?id=" . $task_id);
exit;
