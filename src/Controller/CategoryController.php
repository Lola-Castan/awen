<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\EventCategory;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/products/category/{id}', name: 'app_products_by_category')]
    public function productsByCategory(Category $category): Response
    {
        return $this->render('category/products.html.twig', [
            'category' => $category,
            'products' => $category->getProducts(),
        ]);
    }

    #[Route('/events/category/{id}', name: 'app_events_by_category')]
    public function eventsByCategory(EventCategory $category): Response
    {
        return $this->render('category/events.html.twig', [
            'category' => $category,
            'events' => $category->getEvents(),
        ]);
    }

    #[Route('/category/{id}/creators', name: 'app_category_creators')]
    public function creatorsByCategory(Category $category, UserRepository $userRepository): Response
    {
        $creators = $userRepository->findByRoleAndCategory('ROLE_CREATOR', $category);

        return $this->render('category/creators.html.twig', [
            'category' => $category,
            'creators' => $creators,
        ]);
    }
} 