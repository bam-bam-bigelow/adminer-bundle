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
	public function index(): Response {
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
			$html = (string)$html;
		}

		return new Response($html, 200, [
			'Content-Type' => 'text/html; charset=UTF-8',
		]);
	}
}