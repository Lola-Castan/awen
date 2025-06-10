<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepository, EventRepository $eventRepository): Response
    {
        // Récupérer les 10 derniers posts publiés
        $latestPosts = $postRepository->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Récupérer les événements à venir publiés
        $upcomingEvents = $eventRepository->findUpcomingPublished();

        return $this->render('home/index.html.twig', [
            'latestPosts' => $latestPosts,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
