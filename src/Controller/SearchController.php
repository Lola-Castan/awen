<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    public function __construct(
        private SearchService $searchService
    ) {
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $form = $this->createForm(SearchType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false
        ]);

        $form->handleRequest($request);
        $results = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();
            $results = $this->searchService->search($query);
        }

        return $this->render('search/index.html.twig', [
            'form' => $form,
            'results' => $results,
        ]);
    }

    public function searchBar(): Response
    {
        $form = $this->createForm(SearchType::class, null, [
            'method' => 'GET',
            'action' => $this->generateUrl('app_search'),
            'csrf_protection' => false
        ]);

        return $this->render('search/_search_bar.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 