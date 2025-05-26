<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    /**
     * @return User[] Returns an array of active creators
     */
    public function findCreators(?Category $category = null, ?string $sort = 'newest'): array
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.roles', 'r')
            ->andWhere('r.name = :role')
            ->setParameter('role', 'ROLE_CREATOR');

        if ($category) {
            $qb->innerJoin('u.categories', 'cat')
               ->andWhere('cat.id = :categoryId')
               ->setParameter('categoryId', $category->getId());
        }

        // Appliquer le tri
        switch ($sort) {
            case 'name_asc':
                $qb->orderBy('u.username', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('u.username', 'DESC');
                break;
            case 'products_count':
                $qb->leftJoin('u.products', 'p')
                   ->groupBy('u.id')
                   ->orderBy('COUNT(p.id)', 'DESC');
                break;
            case 'newest':
            default:
                $qb->orderBy('u.createdAt', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return User[] Returns an array of User objects with the specified role and category
     */
    public function findByRoleAndCategory(string $role, Category $category): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.roles', 'r')
            ->andWhere('r.name = :role')
            ->andWhere(':category MEMBER OF u.categories')
            ->setParameter('role', $role)
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
