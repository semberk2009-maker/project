<?php
$host = 'localhost';
$database = 'muziekopdrachtdb'; 
$user = 'root';     
$wachtwoord = 'root'; 

$conn = new mysqli($host, $user, $wachtwoord, $database);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $wachtwoord);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database verbinding mislukt: " . $e->getMessage());
}
?>