<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Enum\ProductStatus;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{
    #[Route('/products', name: 'products')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->get('category');
        $sort = $request->query->get('sort', 'newest'); // Par défaut, tri par date de création

        $category = null;
        if ($categoryId) {
            $category = $categoryRepository->find($categoryId);
        }

        $queryBuilder = $productRepository->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 'published');

        if ($category) {
            $queryBuilder
                ->innerJoin('p.categories', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $category->getId());
        }

        // Appliquer le tri
        switch ($sort) {
            case 'price_asc':
                $queryBuilder->orderBy('p.price', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('p.price', 'DESC');
                break;
            case 'name_asc':
                $queryBuilder->orderBy('p.name', 'ASC');
                break;
            case 'name_desc':
                $queryBuilder->orderBy('p.name', 'DESC');
                break;
            case 'newest':
            default:
                $queryBuilder->orderBy('p.createdAt', 'DESC');
                break;
        }

        $products = $queryBuilder->getQuery()->getResult();
        $categories = $categoryRepository->findAll();

        $sortOptions = [
            'newest' => 'Plus récents',
            'price_asc' => 'Prix croissant',
            'price_desc' => 'Prix décroissant',
            'name_asc' => 'Nom A-Z',
            'name_desc' => 'Nom Z-A',
        ];

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'current_category' => $category,
            'current_sort' => $sort,
            'sort_options' => $sortOptions,
        ]);
    }
    
    #[Route('/product/{id}', name: 'product_show')]
    public function show(Product $product): Response
    {
        // Vérifie si le produit est publié, sinon renvoie une 404
        if ($product->getStatus() !== ProductStatus::Published) {
            throw $this->createNotFoundException('Ce produit n\'est pas disponible.');
        }
        
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/create', name: 'product_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setName($form->get('name')->getData());
            $product->setShortDescription($form->get('shortDescription')->getData());
            $product->setLongDescription($form->get('longDescription')->getData());
            $product->setStock($form->get('stock')->getData());
            $product->setWeight($form->get('weight')->getData());
            $product->setWidth($form->get('width')->getData());
            $product->setDepth($form->get('depth')->getData());
            $product->setHeight($form->get('height')->getData());
            $product->setPrice($form->get('price')->getData());
            $product->setShowcaseProduct($form->get('showcaseProduct')->getData());
            $product->setStatus(ProductStatus::Draft);
            $product->setCreatedAt(new \DateTimeImmutable());

            // todo : add more validation ?

            $entityManager->persist($product);
            $entityManager->flush();
        }

        return $this->render('product/create.html.twig', [
            'productForm' => $form,
        ]);
    }

}
