<?php
session_start();
require_once 'config.php';

// Инициализация переменных
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user = null;

// Обновление сессий
$time = time();
$stmt = $pdo->prepare("REPLACE INTO sessions (user_id, last_activity) VALUES (?, ?)");
$stmt->execute([$user_id, $time]);

// Получение данных пользователя, если авторизован
if ($user_id) {
    $stmt = $pdo->prepare("SELECT username, email, is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($user) {
        $_SESSION['username'] = $user['username']; // Для совместимости с шаблоном
    }
}

// Обработка параметров showforum и showtopic
if (isset($_GET['showforum'])) {
    $forum_id = (int)$_GET['showforum'];
    $stmt = $pdo->prepare("SELECT name, description FROM forums WHERE id = ?");
    $stmt->execute([$forum_id]);
    $forum = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($forum) {
        // Получение тем форума
        $topicsStmt = $pdo->prepare("SELECT t.id, t.title, t.created_at, u.username, (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) as post_count, (SELECT MAX(post_date) FROM posts p WHERE p.topic_id = t.id) as last_post_date FROM topics t LEFT JOIN users u ON t.user_id = u.id WHERE t.forum_id = ? ORDER BY last_post_date DESC");
        $topicsStmt->execute([$forum_id]);
        $topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Отображение страницы форума
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <title><?php echo htmlspecialchars($forum['name']); ?> - Мой Форум</title>
            <link rel="stylesheet" href="/style.css">
        </head>
        <body>
            <div id="ipbwrapper">
                <h1><?php echo htmlspecialchars($forum['name']); ?></h1>
                <p><?php echo htmlspecialchars($forum['description']); ?></p>
                <table class="ipbtable" cellspacing="1">
                    <tr>
                        <th>Тема</th>
                        <th>Автор</th>
                        <th>Сообщений</th>
                        <th>Последнее сообщение</th>
                    </tr>
                    <?php foreach ($topics as $topic): ?>
                        <tr>
                            <td class="row2"><a href="/index.php?showtopic=<?php echo $topic['id']; ?>"><?php echo htmlspecialchars($topic['title']); ?></a></td>
                            <td class="row1"><?php echo htmlspecialchars($topic['username']); ?></td>
                            <td class="row1"><?php echo $topic['post_count']; ?></td>
                            <td class="row1"><?php echo $topic['last_post_date'] ? date('d.m.Y, H:i', strtotime($topic['last_post_date'])) : 'Нет сообщений'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <a href="/index.php">Назад на главную</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
} elseif (isset($_GET['showtopic'])) {
    $topic_id = (int)$_GET['showtopic'];
    $stmt = $pdo->prepare("SELECT t.title, t.forum_id, f.name as forum_name FROM topics t JOIN forums f ON t.forum_id = f.id WHERE t.id = ?");
    $stmt->execute([$topic_id]);
    $topic = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($topic) {
        // Получение сообщений темы
        $postsStmt = $pdo->prepare("SELECT p.content, p.post_date, u.username FROM posts p LEFT JOIN users u ON p.user_id = u.id WHERE p.topic_id = ? ORDER BY p.post_date ASC");
        $postsStmt->execute([$topic_id]);
        $posts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Отображение страницы темы
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <title><?php echo htmlspecialchars($topic['title']); ?> - Мой Форум</title>
            <link rel="stylesheet" href="/style.css">
        </head>
        <body>
            <div id="ipbwrapper">
                <h1><?php echo htmlspecialchars($topic['title']); ?></h1>
                <p>Форум: <a href="/index.php?showforum=<?php echo $topic['forum_id']; ?>"><?php echo htmlspecialchars($topic['forum_name']); ?></a></p>
                <table class="ipbtable" cellspacing="1">
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td class="row2">
                                <strong><?php echo htmlspecialchars($post['username']); ?></strong><br>
                                <?php echo date('d.m.Y, H:i', strtotime($post['post_date'])); ?>
                            </td>
                            <td class="row1"><?php echo htmlspecialchars($post['content']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <a href="/index.php?showforum=<?php echo $topic['forum_id']; ?>">Назад к форуму</a> | <a href="/index.php">На главную</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Получение статистики форума (для главной страницы)
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$last_user = $pdo->query("SELECT id, username FROM users ORDER BY reg_date DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$online_total = $pdo->query("SELECT COUNT(*) FROM sessions WHERE last_activity > " . (time() - 900))->fetchColumn();
$online_guests = $pdo->query("SELECT COUNT(*) FROM sessions WHERE user_id = 0 AND last_activity > " . (time() - 900))->fetchColumn();
$online_users = $pdo->query("SELECT COUNT(*) FROM sessions WHERE user_id > 0 AND last_activity > " . (time() - 900))->fetchColumn();
$online_hidden = 0; // Уточните, если есть столбец is_hidden
$future_events = []; // Адаптируйте под таблицу событий, если есть
$record_visitors = 10182;
$record_date = '25.12.2016, 6:52';

// Получение данных для отображения разделов и форумов (для главной страницы)
$sections = $pdo->query("SELECT * FROM sections")->fetchAll(PDO::FETCH_ASSOC);
$forums = [];
foreach ($sections as $section) {
    $section_id = $section['id'];
    $forumStmt = $pdo->prepare("
        SELECT f.*, 
               (SELECT COUNT(*) FROM topics t WHERE t.forum_id = f.id) as topic_count, 
               (SELECT COUNT(*) FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.forum_id = f.id) as post_count,
               (SELECT p.post_date FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.forum_id = f.id ORDER BY p.post_date DESC LIMIT 1) as last_post_date,
               (SELECT t.id FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.forum_id = f.id ORDER BY p.post_date DESC LIMIT 1) as last_topic_id
        FROM forums f 
        WHERE f.section_id = ? AND f.parent_forum_id IS NULL
    ");
    $forumStmt->execute([$section_id]);
    $forums[$section_id] = $forumStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($forums[$section_id] as &$forum) {
        if ($forum['last_post_date']) {
            $forum['last_post'] = [
                'post_date' => strtotime($forum['last_post_date']),
                'topic_id' => $forum['last_topic_id']
            ];
        }
        $subforumStmt = $pdo->prepare("
            SELECT f.*, 
                   (SELECT COUNT(*) FROM topics t WHERE t.forum_id = f.id) as topic_count, 
                   (SELECT COUNT(*) FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.forum_id = f.id) as post_count,
                   (SELECT p.post_date FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.forum_id = f.id ORDER BY p.post_date DESC LIMIT 1) as last_post_date,
                   (SELECT t.id FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.forum_id = f.id ORDER BY p.post_date DESC LIMIT 1) as last_topic_id
            FROM forums f 
            WHERE f.parent_forum_id = ?
        ");
        $subforumStmt->execute([$forum['id']]);
        $subforums = $subforumStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($subforums as &$subforum) {
            if ($subforum['last_post_date']) {
                $subforum['last_post'] = [
                    'post_date' => strtotime($subforum['last_post_date']),
                    'topic_id' => $subforum['last_topic_id']
                ];
            }
        }
        $forum['subforums'] = $subforums;
    }
    unset($forum); // Сбрасываем ссылку после цикла
}

// Подключение HTML-шаблона главной страницы
include 'templates/index.html';
?>