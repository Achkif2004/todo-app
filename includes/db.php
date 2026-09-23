<?php
$host = 'JOUW_DB_HOST';
$db = 'JOUW_DB_NAAM';
$user = 'JOUW_DB_GEBRUIKER';
$pass = 'JOUW_DB_WACHTWOORD'; // echte gegevens nooit naar GitHub pushen

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database-verbinding mislukt: " . $e->getMessage());
}
?>
