<?php

namespace App\Repository\Trait;

use App\Dto\PaginatedResultDto;
use App\Service\Phrases;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait AdvancedSearchTrait
{
    abstract protected function getSearchableColumns(): array;
    
    abstract protected function getEntityAlias(): string;
    
    abstract protected function getDtoClassName(): string;
    
    public function advancedSearch(
        string $keywords,
        string $sort,
        int $page = 1,
        int $limit = 10
    ): PaginatedResultDto
    {
        $queryBuilder = $this->createQueryBuilder($this->getEntityAlias());        
        $columns = $this->getSearchableColumns();
        $phrases = $this->extractPhrases($keywords);
        list($column, $direction) = $this->getOrderBy($sort);
        
        foreach ($phrases as $phrase) {
            $parameter = sprintf('search_%s', uniqid());
            
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        ...array_map(
                            fn (string $column) => $queryBuilder->expr()->like(
                                $queryBuilder->expr()->lower($column),
                                $queryBuilder->expr()->lower(":{$parameter}")
                            ),
                            $columns
                        )
                    )
                )
                ->setParameter($parameter, "%$phrase%");
        }
        
        $queryBuilder->orderBy($column, $direction);
        
        $queryBuilder
            ->setFirstResult($from = (($page - 1) * $limit))
            ->setMaxResults($limit);
        
        $paginator = new Paginator($queryBuilder);
        
        $entities = array_map(
            function ($entity) {
                $dto = $this->getDtoClassName();
                return $dto::fromEntity($entity);
            },
            iterator_to_array($paginator)
        );
        
        return new PaginatedResultDto(
            $entities,
            $total = $paginator->count(),
            $page,
            $limit,
            (int) ceil($total / $limit)
        );
    }
    
    private function extractPhrases(string $keywords): array
    {
        return (new Phrases($keywords))->extract();
    }
    
    private function getOrderBy(string $sort): array
    {
        $column = $sort;
        $direction = 'ASC';
        
        if (str_starts_with($sort, '-')) {
            $column = substr($sort, 1);
            $direction = 'DESC';
        }
        
        return [$column, $direction];
    }
}