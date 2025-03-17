<?php
session_start();
require_once 'config.php';

$forum_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM forums WHERE id = ?");
$stmt->execute([$forum_id]);
$forum = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$forum) {
    die("Форум не найден");
}

// Подключение HTML-шаблона
include 'templates/admin.html';
?>