<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use App\Enum\ProductStatus;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
    
    /**
     * Trouve tous les produits publiés avec options de tri et filtrage par catégorie
     * @param Category|null $category La catégorie pour filtrer les produits
     * @param string $sort Le critère de tri ('newest', 'price_asc', 'price_desc', 'name_asc', 'name_desc')
     * @return Product[] Returns an array of published Product objects
     */
    public function findSortedPublishedProducts(?Category $category = null, string $sort = 'newest'): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', ProductStatus::Published);

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

        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Trouve les produits mis en avant (showcaseProduct = true)
     * @return Product[] Returns an array of featured Product objects
     */
    public function findFeaturedProducts(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.showcaseProduct = :showcase')
            ->setParameter('status', ProductStatus::Published)
            ->setParameter('showcase', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
