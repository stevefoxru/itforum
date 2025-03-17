<?php
session_start();
require_once 'config.php';

$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT t.*, f.name AS forum_name FROM topics t JOIN forums f ON t.forum_id = f.id WHERE t.id = ?");
$stmt->execute([$topic_id]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$topic) {
    die("Тема не найдена");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($topic['title']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="ipbwrapper">
        <div id="logostrip"></div>
        <div id="submenu">
            <div class="ipb-top-right-link"><a href="index.php">Главная</a></div>
            <div class="ipb-top-right-link"><a href="forum.php?id=<?php echo $topic['forum_id']; ?>">Назад к форуму</a></div>
        </div>
        
        <div class="borderwrap">
            <h3 class="maintitle"><?php echo htmlspecialchars($topic['title']); ?></h3>
            <table class="ipbtable" cellspacing="1">
                <tr>
                    <th>Автор</th>
                    <th>Сообщение</th>
                    <th>Дата</th>
                </tr>
                <?php
                $stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.topic_id = ? ORDER BY p.created_at ASC");
                $stmt->execute([$topic_id]);
                $row_class = 'post1';
                while ($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td class='$row_class'>" . htmlspecialchars($post['username']) . "</td>";
                    echo "<td class='$row_class postcolor'>" . nl2br(htmlspecialchars($post['content'])) . "</td>";
                    echo "<td class='$row_class'>" . $post['created_at'] . "</td>";
                    echo "</tr>";
                    $row_class = ($row_class == 'post1') ? 'post2' : 'post1'; // Чередование фона
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>