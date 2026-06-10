<?php

namespace App\Repository;

use App\Entity\Book;
use App\Service\Phrases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }
    
    public function advancedSearch(string $keywords, string $sort): array
    {
        $queryBuilder = $this->createQueryBuilder('b');
        
        $columns = $this->getSearchableColumns();
        $phrases = (new Phrases($keywords))->extract();
        $orderBy = $this->getOrderBy($sort);
        
        foreach ($phrases as $phrase) {
            $parameter = sprintf('search_%s', uniqid());
            
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                       ...array_map(fn (string $column) => $queryBuilder->expr()->like($column, ":{$parameter}"), $columns)
                    )
                )
                ->setParameter($parameter, "%$phrase%");
        }
        
        return $queryBuilder
            ->orderBy($orderBy['column'], $orderBy['direction'])
            ->getQuery()
            ->getResult();
    }
    
    private function getSearchableColumns(): array
    {
        return ['b.title', 'b.isbn'];
    }
    
    private function getOrderBy(string $sort): array
    {
        $column = $sort;
        $direction = 'ASC';
        
        if (str_starts_with($sort, '-')) {
            $column = substr($sort, 1);
            $direction = 'DESC';
        }
        
        return [
            'column' => $column,
            'direction' => $direction
        ];
    }
}
