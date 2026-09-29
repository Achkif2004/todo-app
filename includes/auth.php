<?php
// Gedeelde hulpfuncties voor sessies en toegangscontrole.

/**
 * Stuurt de bezoeker naar de loginpagina als die niet ingelogd is.
 * Geeft het id van de ingelogde gebruiker terug.
 */
function require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
    return (int) $_SESSION['user_id'];
}

/**
 * Haalt een taak op, maar enkel als ze bij een lijst van deze gebruiker hoort.
 * Geeft null terug als de taak niet bestaat of van iemand anders is.
 */
function find_owned_task(PDO $conn, int $taskId, int $userId): ?array
{
    $stmt = $conn->prepare("
        SELECT t.*
        FROM tasks t
        JOIN lists l ON t.list_id = l.id
        WHERE t.id = ? AND l.user_id = ?
    ");
    $stmt->execute([$taskId, $userId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    return $task ?: null;
}
