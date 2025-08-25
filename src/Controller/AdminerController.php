<?php

namespace YourVendor\AdminerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminerController extends AbstractController
{
	/**
	 * @Route("/admin/adminer", name="adminer")
	 * @return Response
	 */
	public function index(): Response {
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

		// Prevent any headers from being sent early
		if (headers_sent()) {
			throw new \Exception('Headers already sent');
		}

		ob_start();
		// Suppress any potential output that might interfere with headers
		ob_implicit_flush(false);
		
		require __DIR__ . '/../../var/adminer.php'; // Adminer файл
		$content = ob_get_clean();

		// Create response with proper content type
		$response = new Response($content);
		$response->headers->set('Content-Type', 'text/html; charset=utf-8');
		
		return $response;
	}
}