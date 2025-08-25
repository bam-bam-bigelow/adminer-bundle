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

		// Parse DATABASE_URL and set connection parameters
		$this->setAdminerConnectionParams();

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

	private function setAdminerConnectionParams(): void {
		// Get DATABASE_URL from environment
		$databaseUrl = $_ENV['DATABASE_URL'] ?? null;
		
		if (!$databaseUrl) {
			return; // No auto-connection if DATABASE_URL not found
		}

		// Parse DATABASE_URL (format: mysql://user:password@host:port/database)
		$parsedUrl = parse_url($databaseUrl);
		
		if (!$parsedUrl) {
			return;
		}

		// Set connection parameters for Adminer via $_GET superglobal
		if (isset($parsedUrl['scheme'])) {
			$_GET['driver'] = $parsedUrl['scheme'] === 'mysql' ? 'server' : $parsedUrl['scheme'];
		}
		
		if (isset($parsedUrl['host'])) {
			$_GET['server'] = $parsedUrl['host'];
			if (isset($parsedUrl['port'])) {
				$_GET['server'] .= ':' . $parsedUrl['port'];
			}
		}
		
		if (isset($parsedUrl['user'])) {
			$_GET['username'] = $parsedUrl['user'];
		}
		
		if (isset($parsedUrl['path'])) {
			$_GET['db'] = ltrim($parsedUrl['path'], '/');
		}

		// Don't set password in $_GET for security reasons - user will need to enter it
	}
}