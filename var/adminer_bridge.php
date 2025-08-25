<?php

declare(strict_types=1);
/**
 * Этот файл исполняет adminer.php внутри своего namespace, где
 * определены заглушки для ob_flush()/flush()/header()/setcookie() и т.д.
 * В конце возвращает собранный HTML одной строкой (return $buffer).
 */

namespace AdminerSandbox;

/**
 * Заглушка для ob_flush(): ничего не делает.
 * Adminer будет звать ob_flush(), но попадёт сюда — и это безопасно.
 */
function ob_flush(): void {
	// noop
}


function flush(): void {
	// noop
}


function session_start(): void {
	// noop
}


function header(string $header = '', bool $replace = true, int $response_code = 0): void {
	// noop - игнорируем любые header() из adminer.php
}


function session_write_close(): bool {
	// noop - игнорируем закрытие сессии
	return true;
}


function session_regenerate_id(bool $delete_old_session = false): bool {
	// noop - игнорируем регенерацию ID сессии
	return true;
}

/**
 * Стартуем буфер: всё, что выведет adminer.php, попадёт сюда.
 * ВАЖНО: возвращаем пустую строку из колбэка — наружу ничего не уйдёт.
 */
$buffer = '';
ob_start(static function (string $chunk) use (&$buffer): string {
	$buffer .= $chunk;

	return ''; // Ничего не отдаём клиенту
}, 0);

/** @noinspection PhpStrictTypeCheckingInspection */
ob_implicit_flush(false);

// Подключаем реальный adminer.php (лежит рядом, в этом же каталоге var/)
$adminerPath = __DIR__ . '/adminer.php';
$adminerPathModified = __DIR__ . '/adminer_modified.php';
if (!\is_file($adminerPath)) {
	// Завершаем буфер и бросаем исключение в «верхний» код
	if (\ob_get_level() > 0) {
		\ob_end_clean();
	}
	throw new \RuntimeException('Adminer file not found: ' . $adminerPath);
}

if (!file_exists($adminerPathModified)) {
	copy($adminerPath, $adminerPathModified);
	// replace session_start() with our stub
	$adminerCode = file_get_contents($adminerPathModified);
	$adminerCode = preg_replace('#session_start\(\);#', '\AdminerSandbox\session_start();', $adminerCode);
	$adminerCode = preg_replace('#session_regenerate_id\(\);#', '\AdminerSandbox\session_regenerate_id();', $adminerCode);
	$adminerCode = preg_replace('#ob\_flush\(\);#', '\AdminerSandbox\ob_flush();', $adminerCode);
	$adminerCode = preg_replace('#;flush\(\);#', '; \AdminerSandbox\flush();', $adminerCode);
	$adminerCode = preg_replace('#session_write_close\(\);#', '\AdminerSandbox\session_write_close();', $adminerCode);
	$adminerCode = preg_replace('#}header\(#', '} \AdminerSandbox\header(', $adminerCode);
	$adminerCode = preg_replace('#;header\(#', '; \AdminerSandbox\header(', $adminerCode);
	file_put_contents($adminerPathModified, $adminerCode);
}

require $adminerPathModified;

// Закрываем буфер (колбэк проглотит остатки вывода)
if (\ob_get_level() > 0) {
	\ob_end_flush();
}

/**
 * Возвращаем собранный HTML строкой.
 * В контроллере ($html = require '.../adminer_bridge.php';)
 * мы получим именно эту строку.
 */
return $buffer;