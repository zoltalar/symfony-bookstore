<?php

namespace App\Repository;

use App\Dto\Api\BookDto;
use App\Entity\Book;
use App\Repository\Trait\AdvancedSearchTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Override;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    use AdvancedSearchTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }
    
    #[Override]
    protected function getDtoClassName(): string
    {
        return BookDto::class;
    }
    
    #[Override]
    protected function getSearchableColumns(): array
    {
        return array_map(function (string $column) {
            return implode('.', [$this->getEntityAlias(), $column]);
        }, ['title', 'isbn']);
    }

    #[Override]
    protected function getEntityAlias(): string
    {
        return 'b';
    }
}
