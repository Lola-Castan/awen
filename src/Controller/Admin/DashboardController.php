<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\Post;
use App\Entity\Event;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\EventCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
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
	}	public function configureMenuItems(): iterable
	{
		yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
		
		// Gestion des produits et catégories
		yield MenuItem::section('Catalogue');
		yield MenuItem::linkToCrud('Produits', 'fa fa-shopping-bag', Product::class);
		yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Category::class);
		
		// Gestion des événements
		yield MenuItem::section('Événements');
		yield MenuItem::linkToCrud('Événements', 'fa fa-calendar', Event::class);
		yield MenuItem::linkToCrud('Catégories d\'événements', 'fa fa-list', EventCategory::class);
		
		// Gestion du contenu
		yield MenuItem::section('Contenu');
		yield MenuItem::linkToCrud('Posts', 'fa fa-newspaper', Post::class);
		yield MenuItem::linkToCrud('Images', 'fa fa-image', Image::class);
		
		// Gestion des utilisateurs
		yield MenuItem::section('Utilisateurs');
		yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
		yield MenuItem::linkToCrud('Rôles', 'fa fa-user-shield', Role::class);
		
		// Lien de retour au site
		yield MenuItem::section('Navigation');
		yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_home');
	}
}
