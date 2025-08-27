<?php
$host = 'ID474795_planyourtrip.db.webhosting.be';
$db = 'ID474795_planyourtrip'; 
$user = 'ID474795_planyourtrip';
$pass = 'T22OH6n6x145x806s3uw'; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database-verbinding mislukt: " . $e->getMessage());
}
?>