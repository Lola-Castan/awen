<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventUser;
use App\Entity\EventCategory;
use App\Enum\EventStatus;
use App\Enum\EventUserStatus;
use App\Repository\EventRepository;
use App\Repository\EventUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/events')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(
        Request $request,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les paramètres de filtre
        $categoryId = $request->query->get('category');
        $period = $request->query->get('period', 'upcoming');
        $sort = $request->query->get('sort', 'upcoming');

        // Récupérer la catégorie si un ID est fourni
        $category = null;
        if ($categoryId) {
            $category = $entityManager->getRepository(EventCategory::class)->find($categoryId);
        }

        // Options de tri disponibles
        $sortOptions = [
            'upcoming' => 'À venir',
            'date_asc' => 'Date (croissant)',
            'date_desc' => 'Date (décroissant)',
            'title_asc' => 'Nom (A-Z)',
            'title_desc' => 'Nom (Z-A)',
            'popular' => 'Popularité'
        ];

        // Options de période disponibles
        $periodOptions = [
            'all' => 'Tous',
            'upcoming' => 'À venir',
            'today' => "Aujourd'hui",
            'week' => 'Cette semaine',
            'month' => 'Ce mois',
            'past' => 'Passés'
        ];

        // Récupérer toutes les catégories pour le filtre
        $categories = $entityManager->getRepository(EventCategory::class)->findAll();

        // Récupérer les événements filtrés
        $events = $eventRepository->findFiltered($category, $period, $sort);
        
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'categories' => $categories,
            'current_category' => $category,
            'sort_options' => $sortOptions,
            'current_sort' => $sort,
            'period_options' => $periodOptions,
            'current_period' => $period,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event, EventUserRepository $eventUserRepository): Response
    {
        // Récupérer les organisateurs, participants et personnes intéressées
        $organizers = $eventUserRepository->findUsersByEventAndStatus($event, EventUserStatus::ORGANIZER);
        $participants = $eventUserRepository->findUsersByEventAndStatus($event, EventUserStatus::PARTICIPANT);
        $interestedUsers = $eventUserRepository->findUsersByEventAndStatus($event, EventUserStatus::INTERESTED);
        
        // Compter les participants
        $participantsCount = $eventUserRepository->countUsersByEventAndStatus($event, EventUserStatus::PARTICIPANT);
        
        // Déterminer le statut actuel de l'utilisateur connecté par rapport à cet événement
        $currentUserStatus = null;
        if ($this->getUser()) {
            $relation = $eventUserRepository->findRelation($event, $this->getUser());
            if ($relation) {
                $currentUserStatus = $relation->getStatus();
            }
        }
        
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'organizers' => $organizers,
            'participants' => $participants,
            'interestedUsers' => $interestedUsers,
            'participantsCount' => $participantsCount,
            'currentUserStatus' => $currentUserStatus,
        ]);
    }
    
    /**
     * Marquer l'utilisateur comme intéressé par un événement
     */
    #[Route('/{id}/interest', name: 'app_event_interest', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAsInterested(Event $event, Request $request, EntityManagerInterface $entityManager, EventUserRepository $eventUserRepository): Response
    {
        $this->validateCsrfToken($request, 'event_interest_' . $event->getId());
        
        // Vérifier si l'utilisateur a déjà un statut pour cet événement
        $relation = $eventUserRepository->findRelation($event, $this->getUser());
        
        // Si l'utilisateur est déjà intéressé, on annule son intérêt
        if ($relation && $relation->getStatus() === EventUserStatus::INTERESTED) {
            $entityManager->remove($relation);
            $this->addFlash('success', 'Vous n\'êtes plus intéressé(e) par cet événement.');
        } 
        // Si l'utilisateur a un autre statut, on lui demande de confirmer
        elseif ($relation) {
            $this->addFlash('error', 'Vous avez déjà un autre statut pour cet événement.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }
        // Sinon, on le marque comme intéressé
        else {
            $event->addUserWithStatus($this->getUser(), EventUserStatus::INTERESTED);
            $this->addFlash('success', 'Vous êtes maintenant intéressé(e) par cet événement.');
        }
        
        $entityManager->flush();
        
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
    
    /**
     * Marquer l'utilisateur comme participant à un événement
     */
    #[Route('/{id}/join', name: 'app_event_join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function joinEvent(Event $event, Request $request, EntityManagerInterface $entityManager, EventUserRepository $eventUserRepository): Response
    {
        $this->validateCsrfToken($request, 'event_join_' . $event->getId());
        
        // Vérifier si l'utilisateur a déjà un statut pour cet événement
        $relation = $eventUserRepository->findRelation($event, $this->getUser());
        
        // Si l'utilisateur est déjà participant, on annule sa participation
        if ($relation && $relation->getStatus() === EventUserStatus::PARTICIPANT) {
            $entityManager->remove($relation);
            $this->addFlash('success', 'Vous n\'êtes plus inscrit(e) à cet événement.');
        } 
        // Si l'utilisateur est organisateur, il ne peut pas annuler
        elseif ($relation && $relation->getStatus() === EventUserStatus::ORGANIZER) {
            $this->addFlash('error', 'Vous êtes organisateur de cet événement et ne pouvez pas vous désinscrire.');
        }
        // Sinon, on met à jour ou crée un nouveau statut
        else {
            if ($relation) {
                $relation->setStatus(EventUserStatus::PARTICIPANT);
            } else {
                $event->addUserWithStatus($this->getUser(), EventUserStatus::PARTICIPANT);
            }
            $this->addFlash('success', 'Vous êtes maintenant inscrit(e) à cet événement.');
        }
        
        $entityManager->flush();
        
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
    
    /**
     * Valide le jeton CSRF
     */
    private function validateCsrfToken(Request $request, string $tokenId): void
    {
        if (!$this->isCsrfTokenValid($tokenId, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }
    }
}