<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

trait SearchableTrait
{
    /**
     * Ajoute les conditions de recherche à un QueryBuilder
     */
    protected function addSearchConditions(QueryBuilder $qb, string $search, array $fields): QueryBuilder
    {
        if (empty($search)) {
            return $qb;
        }

        $searchTerms = explode(' ', $search);
        $conditions = [];

        foreach ($searchTerms as $index => $term) {
            $termConditions = [];
            foreach ($fields as $field) {
                $termConditions[] = $field . ' LIKE :search' . $index;
            }
            $conditions[] = '(' . implode(' OR ', $termConditions) . ')';
            $qb->setParameter('search' . $index, '%' . $term . '%');
        }

        if (!empty($conditions)) {
            $qb->andWhere('(' . implode(' AND ', $conditions) . ')');
        }

        return $qb;
    }
}
