<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CREATOR')]
class DashboardController extends AbstractDashboardController
{
	#[Route('/admin', name: 'admin')]
	public function index(): Response
	{
		return $this->render('admin/dashboard.html.twig');
	}

	public function configureDashboard(): Dashboard
	{
		return Dashboard::new()
			->setTitle('Awen - Administration');
	}

	public function configureMenuItems(): iterable
	{
		yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
		yield MenuItem::linkToCrud('Produits', 'fa fa-shopping-bag', Product::class);
		yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_home');
	}
}
