<?php

declare(strict_types=1);

namespace YourVendor\AdminerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminerController extends AbstractController
{
	/**
	 * @Route("/admin/adminer", name="adminer", methods={"GET","POST"})
	 */
	public function index(): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

		// Никакого вывода ДО include — иначе заголовки улетят
		$bridgePath = \dirname(__DIR__, 2) . '/var/adminer_bridge.php';
		if (!is_file($bridgePath)) {
			throw new \RuntimeException('Adminer bridge not found: ' . $bridgePath);
		}

		// Подключаем мост; он вернёт собранный HTML как строку
		$html = require $bridgePath;
		if (!\is_string($html)) {
			// На всякий случай: если что-то пошло не так — обеспечим строку
			$html = (string) $html;
		}

		$response = new Response($html, 200, [
			'Content-Type'           => 'text/html; charset=UTF-8',
//			'X-Frame-Options'        => 'SAMEORIGIN',
//			'X-Content-Type-Options' => 'nosniff',
//			'Referrer-Policy'        => 'same-origin',
//			'Cache-Control'          => 'no-store, no-cache, must-revalidate, max-age=0',
//			'Pragma'                 => 'no-cache',
		]);

		// Локально ослабим CSP ТОЛЬКО для этой страницы, чтобы Adminer работал
		// (у Adminer встречаются inline-скрипты и new Function/eval).
//		$csp = implode('; ', [
//			"default-src 'self'",
//			"script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: data:",
//			"style-src 'self' 'unsafe-inline'",
//			"img-src 'self' data: blob:",
//			"connect-src 'self' https: http:",
//			"font-src 'self' data:",
//			"object-src 'none'",
//			"base-uri 'self'",
//			"frame-ancestors 'self'",
//		]);
//		$response->headers->set('Content-Security-Policy', $csp);

		return $response;
	}
}