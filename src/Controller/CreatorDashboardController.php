<?php

namespace App\Controller;

use App\Entity\Product;
use App\Enum\ProductStatus;
use App\Form\CreatorProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
	
	#[Route('/products', name: 'app_creator_products')]
	public function products(EntityManagerInterface $entityManager): Response
	{
		// Récupérer tous les produits du créateur connecté, quel que soit leur statut
		$products = $entityManager->getRepository(Product::class)->findBy(
			['creator' => $this->getUser()],
			['createdAt' => 'DESC']
		);
		
		// Organiser les produits par statut
		$productsByStatus = [
			'published' => [],
			'draft' => [],
			'archived' => []
		];
		
		foreach ($products as $product) {
			$status = $product->getStatus()->value;
			$productsByStatus[$status][] = $product;
		}
		
		return $this->render('creator/products.html.twig', [
			'productsByStatus' => $productsByStatus,
			'totalProducts' => count($products)
		]);
	}
	
	#[Route('/product/new', name: 'app_creator_product_new')]
	public function newProduct(Request $request, EntityManagerInterface $entityManager): Response
	{
		$product = new Product();
		$form = $this->createForm(CreatorProductType::class, $product);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			// Assigner automatiquement le créateur connecté
			$product->setCreator($this->getUser());
			$product->setStatus(ProductStatus::Draft);
			$product->setCreatedAt(new \DateTimeImmutable());
			
			$entityManager->persist($product);
			$entityManager->flush();
			
			$this->addFlash('success', 'Votre produit a été créé avec succès ! Vous pouvez maintenant ajouter des images et finaliser sa configuration.');
			
			// Rediriger vers la liste des produits du créateur
			return $this->redirectToRoute('app_creator_products');
		}
		
		return $this->render('creator/product_new.html.twig', [
			'form' => $form->createView(),
		]);
	}
}
