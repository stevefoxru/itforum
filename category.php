<?php
session_start();
require_once 'config.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Категория не найдена");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($category['name']); ?></title>
    <link rel="stylesheet" href="styles.css"> <!-- Подключите тот же стиль из index.php -->
</head>
<body>
    <div id="container">
        <div class="header"><?php echo htmlspecialchars($category['name']); ?></div>
        <div class="topics">
            <table>
                <tr>
                    <th>Тема</th>
                    <th>Автор</th>
                    <th>Дата</th>
                </tr>
                <?php
                $stmt = $pdo->prepare("SELECT t.*, u.username FROM topics t JOIN users u ON t.user_id = u.id WHERE t.category_id = ? ORDER BY t.created_at DESC");
                $stmt->execute([$category_id]);
                while ($topic = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td><a href='topic.php?id={$topic['id']}'>" . htmlspecialchars($topic['title']) . "</a></td>";
                    echo "<td>" . htmlspecialchars($topic['username']) . "</td>";
                    echo "<td>" . $topic['created_at'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>