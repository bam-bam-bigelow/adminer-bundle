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


/**
 * Заглушка для flush(): ничего не делает.
 */
function flush(): void {
	// noop
}


/**
 * Если нужно полностью подавить заголовки, раскомментируй заглушки ниже.
 * По умолчанию их не трогаем: PHP не отправит заголовки, пока мы ничего не отдали в вывод.
 */

// function header(string $header, bool $replace = true, int $response_code = 0): void
// {
//     // noop — игнорируем любые header() из adminer.php
// }
//
// function setcookie(
//     string $name,
//     string $value = "",
//     array|int $expires_or_options = 0,
//     string $path = "",
//     string $domain = "",
//     bool $secure = false,
//     bool $httponly = false
// ): bool {
//     // noop: куки из Adminer нам не нужны в встраиваемом режиме
//     return true;
// }
//
// function headers_sent(?string &$file = null, ?int &$line = null): bool
// {
//     // Сообщаем, что заголовки НЕ отправлены — чтобы Adminer не паниковал
//     $file = null;
//     $line = 0;
//     return false;
// }

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
if (!\is_file($adminerPath)) {
	// Завершаем буфер и бросаем исключение в «верхний» код
	if (\ob_get_level() > 0) {
		\ob_end_clean();
	}
	throw new \RuntimeException('Adminer file not found: ' . $adminerPath);
}

require $adminerPath;

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