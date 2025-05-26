<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventCategory;
use App\Enum\EventStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Récupère les événements ayant un des statuts spécifiés, triés par date de début
     * 
     * @param EventStatus[] $statuses
     * @return Event[]
     */
    public function findByStatuses(array $statuses): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', $statuses)
            ->orderBy('e.startDateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les événements à venir ayant un statut publié
     * 
     * @return Event[]
     */
    public function findUpcomingPublished(): array
    {
        $now = new \DateTimeImmutable();
        
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->andWhere('e.startDateTime > :now')
            ->setParameter('status', EventStatus::Published)
            ->setParameter('now', $now)
            ->orderBy('e.startDateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Event[] Returns an array of filtered events
     */
    public function findFiltered(?EventCategory $category = null, ?string $period = null, ?string $sort = 'upcoming'): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('statuses', [EventStatus::Published, EventStatus::Cancelled]);

        if ($category) {
            $qb->innerJoin('e.eventCategories', 'cat')
               ->andWhere('cat.id = :categoryId')
               ->setParameter('categoryId', $category->getId());
        }

        // Filtrer par période
        $now = new \DateTimeImmutable();
        switch ($period) {
            case 'past':
                $qb->andWhere('e.endDateTime < :now')
                   ->setParameter('now', $now);
                break;
            case 'today':
                $startOfDay = $now->setTime(0, 0);
                $endOfDay = $now->setTime(23, 59, 59);
                $qb->andWhere('e.startDateTime BETWEEN :start AND :end')
                   ->setParameter('start', $startOfDay)
                   ->setParameter('end', $endOfDay);
                break;
            case 'week':
                $endOfWeek = $now->modify('next sunday');
                $qb->andWhere('e.startDateTime BETWEEN :now AND :endOfWeek')
                   ->setParameter('now', $now)
                   ->setParameter('endOfWeek', $endOfWeek);
                break;
            case 'month':
                $endOfMonth = $now->modify('last day of this month');
                $qb->andWhere('e.startDateTime BETWEEN :now AND :endOfMonth')
                   ->setParameter('now', $now)
                   ->setParameter('endOfMonth', $endOfMonth);
                break;
            case 'upcoming':
                $qb->andWhere('e.startDateTime >= :now')
                   ->setParameter('now', $now);
                break;
            case 'all':
            default:
                // Aucun filtre de date
                break;
        }

        // Appliquer le tri
        switch ($sort) {
            case 'date_desc':
                $qb->orderBy('e.startDateTime', 'DESC');
                break;
            case 'date_asc':
                $qb->orderBy('e.startDateTime', 'ASC');
                break;
            case 'title_asc':
                $qb->orderBy('e.title', 'ASC');
                break;
            case 'title_desc':
                $qb->orderBy('e.title', 'DESC');
                break;
            case 'popular':
                $qb->leftJoin('e.eventUsers', 'eu')
                   ->groupBy('e.id')
                   ->orderBy('COUNT(eu.id)', 'DESC');
                break;
            case 'upcoming':
            default:
                $qb->orderBy('e.startDateTime', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
