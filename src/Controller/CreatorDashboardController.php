<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Image;
use App\Entity\Order;
use App\Enum\ProductStatus;
use App\Form\CreatorProductType;
use App\Form\ProductImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

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
	
	#[Route('/product/{id}/edit', name: 'app_creator_product_edit')]
	public function editProduct(Product $product, Request $request, EntityManagerInterface $entityManager): Response
	{
		// Vérifier que le produit appartient bien au créateur connecté
		if ($product->getCreator() !== $this->getUser()) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce produit.');
		}
		
		$form = $this->createForm(CreatorProductType::class, $product);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$product->setUpdatedAt(new \DateTimeImmutable());
			$entityManager->flush();
			
			$this->addFlash('success', 'Votre produit a été mis à jour avec succès !');
			return $this->redirectToRoute('app_creator_products');
		}
		
		return $this->render('creator/product_edit.html.twig', [
			'form' => $form->createView(),
			'product' => $product
		]);
	}
	
	#[Route('/product/{id}/images', name: 'app_creator_product_images')]
	public function manageProductImages(Product $product, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
	{
		// Vérifier que le produit appartient bien au créateur connecté
		if ($product->getCreator() !== $this->getUser()) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce produit.');
		}
		
		$image = new Image();
		$form = $this->createForm(ProductImageType::class, $image);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$imageFile = $form->get('file')->getData();
			
			if ($imageFile) {
				$originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
				$safeFilename = $slugger->slug($originalFilename);
				$newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
				
				try {
					$imageFile->move(
						$this->getParameter('images_directory'),
						$newFilename
					);
							$image->setFilename($newFilename);
					$image->setCreatedAt(new \DateTimeImmutable());
					$image->setAlt($form->get('alt')->getData() ?: $product->getName());
					$image->setTitle($form->get('title')->getData() ?: $product->getName());
					
					$entityManager->persist($image);
					$product->addImage($image);
					$entityManager->flush();
					
					$this->addFlash('success', 'L\'image a été ajoutée avec succès.');
				} catch (FileException $e) {
					$this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
				}
			}
			
			return $this->redirectToRoute('app_creator_product_images', ['id' => $product->getId()]);
		}
		
		return $this->render('creator/product_images.html.twig', [
			'form' => $form->createView(),
			'product' => $product
		]);
	}
	
	#[Route('/product/{id}/image/{imageId}/remove', name: 'app_creator_product_image_remove')]
	public function removeProductImage(Product $product, int $imageId, EntityManagerInterface $entityManager): Response
	{
		// Vérifier que le produit appartient bien au créateur connecté
		if ($product->getCreator() !== $this->getUser()) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce produit.');
		}
		
		$image = $entityManager->getRepository(Image::class)->find($imageId);
		
		if (!$image) {
			throw $this->createNotFoundException('L\'image n\'existe pas.');
		}
		
		$product->removeImage($image);
		$entityManager->flush();
		
		$this->addFlash('success', 'L\'image a été supprimée avec succès.');
		return $this->redirectToRoute('app_creator_product_images', ['id' => $product->getId()]);
	}
	
	#[Route('/product/{id}/image/{imageId}/position', name: 'app_creator_product_image_position')]
	public function updateImagePosition(Product $product, int $imageId, Request $request, EntityManagerInterface $entityManager): Response
	{
		// Vérifier que le produit appartient bien au créateur connecté
		if ($product->getCreator() !== $this->getUser()) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce produit.');
		}
		
		$image = $entityManager->getRepository(Image::class)->find($imageId);
		
		if (!$image) {
			throw $this->createNotFoundException('L\'image n\'existe pas.');
		}
		
		$position = $request->request->get('position');
		if ($position !== null) {
			$image->setPosition((int) $position);
			$entityManager->flush();
			
			$this->addFlash('success', 'La position de l\'image a été mise à jour avec succès.');
		}
		
		return $this->redirectToRoute('app_creator_product_images', ['id' => $product->getId()]);
	}
	
	#[Route('/product/{id}/status/{status}', name: 'app_creator_product_status')]
	public function changeProductStatus(Product $product, string $status, EntityManagerInterface $entityManager): Response
	{
		// Vérifier que le produit appartient bien au créateur connecté
		if ($product->getCreator() !== $this->getUser()) {
			throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce produit.');
		}
		
		// Vérifier que le statut est valide
		$newStatus = match ($status) {
			'publish' => ProductStatus::Published,
			'draft' => ProductStatus::Draft,
			'archive' => ProductStatus::Archived,
			default => throw $this->createNotFoundException('Statut invalide.')
		};
		
		$product->setStatus($newStatus);
		$product->setUpdatedAt(new \DateTimeImmutable());
		$entityManager->flush();
		
		$statusMessages = [
			'publish' => 'publié',
			'draft' => 'passé en brouillon',
			'archive' => 'archivé'
		];
		
		$this->addFlash('success', sprintf('Votre produit a été %s avec succès !', $statusMessages[$status]));
		return $this->redirectToRoute('app_creator_products');	}
	
	#[Route('/orders', name: 'app_creator_dashboard_orders')]
	public function orders(EntityManagerInterface $entityManager): Response
	{
		// Récupérer toutes les commandes contenant des produits du créateur
		$orders = $entityManager->getRepository(Order::class)->findByCreator($this->getUser());
		
		return $this->render('creator/orders.html.twig', [
			'orders' => $orders
		]);
	}
	
	#[Route('/order/{id}', name: 'app_creator_dashboard_order_detail')]
	public function orderDetail(Order $order): Response
	{
		// Vérifier que la commande contient au moins un produit du créateur
		$hasCreatorProduct = false;
		foreach ($order->getOrderDetails() as $detail) {
			if ($detail->getProduct()->getCreator() === $this->getUser()) {
				$hasCreatorProduct = true;
				break;
			}
		}

		if (!$hasCreatorProduct) {
			throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette commande.');
		}

		return $this->render('creator/order_detail.html.twig', [
			'order' => $order
		]);
	}
}
