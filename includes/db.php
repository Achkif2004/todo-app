<?php
$host = 'localhost';
$db = 'todo_app'; 
$user = 'root';
$pass = ''; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database-verbinding mislukt: " . $e->getMessage());
}
?>
