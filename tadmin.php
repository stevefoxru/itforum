<?php
session_start();
require_once 'config.php';

// Проверка прав доступа
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user['is_admin']) {
    header("Location: index.php");
    exit;
}

// Обработка действий
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

if ($action == 'templates') {
    $template_dir = __DIR__ . '/templates';
    $templates = glob($template_dir . '/*.html');
    if (empty($templates)) {
        $template_error = "В папке $template_dir нет файлов .html!";
    }
}

if ($action == 'edit_template' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $file = basename($_POST['file']);
    $content = $_POST['content'];
    $file_path = __DIR__ . '/templates/' . $file;

    if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'html') {
        file_put_contents($file_path, $content);
        header("Location: admin.php?action=templates");
        exit;
    } else {
        $error = "Файл не найден или недоступен! Путь: $file_path, Существует: " . (file_exists($file_path) ? 'Да' : 'Нет') . ", Права: " . (is_readable($file_path) ? 'Читаем' : 'Не читаем');
    }
}

if ($action == 'edit_template' && $_SERVER['REQUEST_METHOD'] != 'POST') {
    $file = basename($_GET['file']);
    $file_path = __DIR__ . '/templates/' . $file;
    if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'html') {
        $content = file_get_contents($file_path);
    } else {
        $error = "Файл не найден или недоступен! Путь: $file_path, Существует: " . (file_exists($file_path) ? 'Да' : 'Нет') . ", Права: " . (is_readable($file_path) ? 'Читаем' : 'Не читаем');
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div id="ipbwrapper">
        <div id="logostrip"></div>
        <div id="submenu">
            <div class="ipb-top-right-link"><a href="index.php">На главную</a></div>
            <div class="ipb-top-right-link"><a href="logout.php">Выход</a></div>
        </div>
        <div class="borderwrap">
            <h3 class="maintitle">Админ-панель</h3>
            <div class="formsubtitle">
                <a href="admin.php">Разделы</a> | 
                <a href="admin.php?action=forums">Форумы</a> | 
                <a href="admin.php?action=users">Пользователи</a> | 
                <a href="admin.php?action=templates">Шаблоны</a>
            </div>

            <?php if ($action == 'templates'): ?>
                <?php if (isset($template_error)): ?>
                    <div class="errorwrap">
                        <h4>Ошибка</h4>
                        <p><?php echo $template_error; ?></p>
                    </div>
                <?php else: ?>
                    <table class="ipbtable" cellspacing="1">
                        <tr>
                            <th>Файл</th>
                            <th>Действия</th>
                        </tr>
                        <?php foreach ($templates as $template): ?>
                            <?php $file = basename($template); ?>
                            <tr>
                                <td class="row2"><?php echo htmlspecialchars($file); ?></td>
                                <td class="row1"><a href="admin.php?action=edit_template&file=<?php echo urlencode($file); ?>">Редактировать</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>

            <?php elseif ($action == 'edit_template'): ?>
                <h4 class="formsubtitle">Редактировать шаблон: <?php echo htmlspecialchars($file); ?></h4>
                <?php if (isset($error)): ?>
                    <div class="errorwrap">
                        <h4>Ошибка</h4>
                        <p><?php echo $error; ?></p>
                    </div>
                <?php else: ?>
                    <form method="post" action="admin.php?action=edit_template" class="formtable">
                        <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                        <table cellspacing="1">
                            <tr>
                                <td class="pformleft">Содержимое:</td>
                                <td class="pformright">
                                    <textarea name="content" class="input-text" rows="20" cols="80" style="width: 100%;"><?php echo htmlspecialchars($content); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="pformstrip">
                                    <input type="submit" value="Сохранить" class="button">
                                    <a href="admin.php?action=templates" class="fauxbutton">Назад</a>
                                </td>
                            </tr>
                        </table>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>