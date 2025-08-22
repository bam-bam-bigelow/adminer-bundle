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

		ob_start();
		require __DIR__ . '/../../var/adminer.php'; // Adminer файл
		$content = ob_get_clean();

		return new Response($content);
	}
}