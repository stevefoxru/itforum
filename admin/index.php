<?php
session_start();
require_once __DIR__ . '/../config.php';

// Проверка прав доступа
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user['is_admin']) {
    header("Location: /index.php");
    exit;
}

// Обработка действий
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Создание раздела
if ($action == 'add_section' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $stmt = $pdo->prepare("INSERT INTO sections (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $description]);
    header("Location: /admin/index.php");
    exit;
}

// Редактирование раздела
if ($action == 'edit_section' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $stmt = $pdo->prepare("UPDATE sections SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $description, $id]);
    header("Location: /admin/index.php");
    exit;
}

// Удаление раздела
if ($action == 'delete_section') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: /admin/index.php");
    exit;
}

// Создание форума
if ($action == 'add_forum' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $section_id = (int)$_POST['section_id'];
    $parent_forum_id = !empty($_POST['parent_forum_id']) ? (int)$_POST['parent_forum_id'] : null;
    $stmt = $pdo->prepare("INSERT INTO forums (name, description, section_id, parent_forum_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $section_id, $parent_forum_id]);
    header("Location: /admin/index.php");
    exit;
}

// Редактирование форума
if ($action == 'edit_forum' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $section_id = (int)$_POST['section_id'];
    $parent_forum_id = !empty($_POST['parent_forum_id']) ? (int)$_POST['parent_forum_id'] : null;
    $stmt = $pdo->prepare("UPDATE forums SET name = ?, description = ?, section_id = ?, parent_forum_id = ? WHERE id = ?");
    $stmt->execute([$name, $description, $section_id, $parent_forum_id, $id]);
    header("Location: /admin/index.php");
    exit;
}

// Удаление форума
if ($action == 'delete_forum') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM forums WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: /admin/index.php");
    exit;
}

// Управление пользователями
if ($action == 'toggle_admin' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE users SET is_admin = NOT is_admin WHERE id = ? AND id != ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    header("Location: /admin/index.php?action=users");
    exit;
}

if ($action == 'delete_user') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    header("Location: /admin/index.php?action=users");
    exit;
}

// Редактирование шаблонов
if ($action == 'templates') {
    $template_dir = __DIR__ . '/../templates';
    $templates = glob($template_dir . '/*.html');
    if (empty($templates)) {
        $template_error = "В папке $template_dir нет файлов .html!";
    }
}

if ($action == 'edit_template' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $file = basename($_POST['file']);
    $content = $_POST['content'];
    $file_path = __DIR__ . '/../templates/' . $file;

    if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'html') {
        file_put_contents($file_path, $content);
        header("Location: /admin/index.php?action=templates");
        exit;
    } else {
        $error = "Файл не найден или недоступен! Путь: $file_path, Существует: " . (file_exists($file_path) ? 'Да' : 'Нет') . ", Права: " . (is_readable($file_path) ? 'Читаем' : 'Не читаем');
    }
}

if ($action == 'edit_template' && $_SERVER['REQUEST_METHOD'] != 'POST') {
    $file = basename($_GET['file']);
    $file_path = __DIR__ . '/../templates/' . $file;
    if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'html') {
        $content = file_get_contents($file_path);
    } else {
        $error = "Файл не найден или недоступен! Путь: $file_path, Существует: " . (file_exists($file_path) ? 'Да' : 'Нет') . ", Права: " . (is_readable($file_path) ? 'Читаем' : 'Не читаем');
    }
}

// Статистика форума
if ($action == 'stats') {
    // Общее количество сообщений
    $total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();

    // Общее количество пользователей
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Последний зарегистрированный пользователь
    $stmt = $pdo->query("SELECT id, username FROM users ORDER BY reg_date DESC LIMIT 1");
    $last_user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Будущие события (за 5 дней, пример)
    $future_events = []; // Добавьте логику для календаря, если есть таблица событий

    // Рекорд посещаемости (статичен, замените на реальные данные, если есть)
    $record_visitors = 10182;
    $record_date = '25.12.2016, 6:52';

    // Онлайн-пользователи (заглушка, так как нет sessions)
    $online_total = 'N/A';
    $online_guests = 'N/A';
    $online_users = 'N/A';
    $online_hidden = 'N/A';
    $online_list = [];
}

// Подготовка данных для шаблона
$sections = $pdo->query("SELECT * FROM sections")->fetchAll(PDO::FETCH_ASSOC);
$forums_data = $pdo->query("SELECT f.*, s.name AS section_name, pf.name AS parent_name FROM forums f LEFT JOIN sections s ON f.section_id = s.id LEFT JOIN forums pf ON f.parent_forum_id = pf.id")->fetchAll(PDO::FETCH_ASSOC);
$users_data = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Подключение HTML-шаблона
include __DIR__ . '/../templates/admin.html';
?>