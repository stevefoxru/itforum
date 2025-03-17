<?php
$file_path = __DIR__ . '/templates/admin.html';
echo "Путь: $file_path<br>";
echo "Существует: " . (file_exists($file_path) ? 'Да' : 'Нет') . "<br>";
echo "Читаем: " . (is_readable($file_path) ? 'Да' : 'Нет') . "<br>";
?>