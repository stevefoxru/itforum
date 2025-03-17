<?php
$host = 'localhost';
$dbname = 'itzuev';
$username = 'itzuev';
$password = 'KFtjzjWwk5CP23Xe';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>