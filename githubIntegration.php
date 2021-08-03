<?php
$key = 'superSecretKey123';
$prefix = '[GitHub] ';

include('db.php');

$payload = file_get_contents('php://input');

// Проверка сигнатуры
$signature = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE']);
$algos = hash_algos();
list($algo, $hash) = $signature;
if (!in_array($algo, $algos)) {
	http_response_code(400);
	die("Unknown hashing algo: {$algo}");
}

$expectedHash = hash_hmac($algo, $payload, $key);
if ($expectedHash !== $hash) {
	http_response_code(403);
	die('Invalid signature');
}

// Получаем массив из payload
$payload = json_decode($payload, true);

// Извлекаем из него сообщения об изменениях
$mesages = [];
foreach ($payload['commits'] as $commit) {
  $messages[] = $commit['message'];
}

// Формируем новость из полученных сообщений
$new = "";
foreach ($messages as $message) {
  $new .= "{$prefix}{$message}\n";
}

// Отрезаем последний (/n)
$new = substr($new, 0, strlen($new)-1);

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
