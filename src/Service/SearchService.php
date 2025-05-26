<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\EventRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\ProductStatus;
use App\Enum\EventStatus;

class SearchService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private ProductRepository $productRepository,
        private EventRepository $eventRepository,
        private PostRepository $postRepository
    ) {
    }

    public function search(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        $queryLike = '%' . strtolower($query) . '%';

        // Recherche des créateurs
        $creators = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from('App\Entity\User', 'u')
            ->leftJoin('u.roles', 'r')
            ->where('r.name = :role')
            ->andWhere('LOWER(u.username) LIKE :query OR LOWER(u.firstName) LIKE :query OR LOWER(u.lastName) LIKE :query OR LOWER(u.creatorInfo.displayName) LIKE :query')
            ->setParameter('role', 'ROLE_CREATOR')
            ->setParameter('query', $queryLike)
            ->getQuery()
            ->getResult();

        // Recherche des produits publiés
        $products = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from('App\Entity\Product', 'p')
            ->where('p.status = :status')
            ->andWhere('LOWER(p.name) LIKE :query OR LOWER(p.shortDescription) LIKE :query')
            ->setParameter('status', ProductStatus::Published)
            ->setParameter('query', $queryLike)
            ->getQuery()
            ->getResult();

        // Recherche des événements publiés
        $events = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from('App\Entity\Event', 'e')
            ->where('e.status = :status')
            ->andWhere('LOWER(e.title) LIKE :query OR LOWER(e.shortDescription) LIKE :query')
            ->setParameter('status', EventStatus::Published)
            ->setParameter('query', $queryLike)
            ->getQuery()
            ->getResult();

        // Recherche des posts publiés
        $posts = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from('App\Entity\Post', 'p')
            ->where('p.isPublished = :published')
            ->andWhere('LOWER(p.title) LIKE :query OR LOWER(p.content) LIKE :query')
            ->setParameter('published', true)
            ->setParameter('query', $queryLike)
            ->getQuery()
            ->getResult();

        return [
            'creators' => $creators,
            'products' => $products,
            'events' => $events,
            'posts' => $posts
        ];
    }
} 