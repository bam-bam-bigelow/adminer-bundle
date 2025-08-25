<?php

declare(strict_types=1);

namespace YourVendor\AdminerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminerController extends AbstractController
{
	/**
	 * @Route("/admin/adminer", name="adminer")
	 */
	public function index(): Response {
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

		// ВАЖНО: ничего не выводить до буферизации (никаких BOM/пробелов)

		// Строка-приёмник всего вывода Adminer
		$sink = '';

		// Включаем буфер с колбэком: любой вывод попадает в $sink, наружу — пусто
		ob_start(static function (string $chunk) use (&$sink): string {
			$sink .= $chunk;

			return ''; // Ничего не отправлять клиенту
		}, 0);

		// Лучше выключить implicit flush на всякий случай
		ob_implicit_flush(false);

		// Лучше выключить implicit flush на всякий случай
		ob_implicit_flush(false);

		// Путь к adminer.php (положи его, например, в var/adminer.php)
		$adminerPath = \dirname(__DIR__, 2) . '/var/adminer.php';

		if (!is_file($adminerPath)) {
			// Закрываем буфер перед исключением
			if (ob_get_level() > 0) {
				ob_end_clean();
			}
			throw new \RuntimeException('Adminer file not found: ' . $adminerPath);
		}

		// Выполняем adminer.php (он будет echo/flush — всё уйдёт в $sink)
		require $adminerPath;

		// ВАЖНО: закрываем буфер так, чтобы колбэк отработал на остатки
		if (ob_get_level() > 0) {
			ob_end_flush(); // вызовет колбэк для хвостов буфера
		}

		// Теперь у нас весь HTML Adminer в $sink и НИ ОДИН заголовок не ушёл клиенту
		return new Response($sink, 200, [
			'Content-Type' => 'text/html; charset=UTF-8'
		]);
	}
}