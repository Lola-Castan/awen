<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\EventCategory;
use App\Repository\CategoryRepository;
use App\Repository\EventCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/products/category/{id}', name: 'products_by_category')]
    public function productsByCategory(Category $category): Response
    {
        return $this->render('category/products.html.twig', [
            'category' => $category,
            'products' => $category->getProducts(),
        ]);
    }

    #[Route('/events/category/{id}', name: 'events_by_category')]
    public function eventsByCategory(EventCategory $category): Response
    {
        return $this->render('category/events.html.twig', [
            'category' => $category,
            'events' => $category->getEvents(),
        ]);
    }

    #[Route('/creators/category/{id}', name: 'creators_by_category')]
    public function creatorsByCategory(Category $category): Response
    {
        return $this->render('category/creators.html.twig', [
            'category' => $category,
            'creators' => $category->getCreators(),
        ]);
    }
} 