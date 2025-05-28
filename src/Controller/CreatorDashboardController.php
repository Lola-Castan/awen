<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CREATOR')]
#[Route('/creator-dashboard')]
class CreatorDashboardController extends AbstractController
{
	#[Route('/', name: 'app_creator_dashboard')]
	public function index(): Response
	{
		return $this->render('creator/dashboard.html.twig', [
			'user' => $this->getUser(),
		]);
	}
}
