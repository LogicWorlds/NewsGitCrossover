<?php
$key = 'superSecretKey123';
$prefix = '[GitHub] ';
if ($_GET['key'] != $key)
  die('Key mismatch!');

include('db.php');

// Получаем массив из payload
$payload = json_decode($_REQUEST['payload'], true);

// Извлекаем из него сообщения об изменениях
$mesages = [];
foreach ($payload['commits'] as $commit) {
  $messages[] = $commit['message'];
}

// Формируем новость из полученных сообщений
$new = "";
foreach ($messages as $message) {
  $new .= "{$prefix}{$message}<br>";
}

// Отрезаем последний <br>
$new = substr($new, 0, strlen($new)-4);

// Добавляем новость в базу
$newss = str_replace("\n", "<br>",  $new);
$newss = mysqli_real_escape_string($mysqli, $newss);
$dates = date("Y-m-d H:i:s");
$mysqli->query("INSERT INTO `l_news` (`date`, `news`) VALUES ('".$dates."', '".$newss."')");

// Обновляем кеш
$m = new Memcached();
$m->addServer('localhost', 11211);
$m->delete('l_news');
?>
