<?php

namespace App\Dto;

use InvalidArgumentException;

final class PaginatedResultDto
{
    public function __construct(
        private readonly array $entities,
        private readonly int $total,
        private readonly int $currentPage,
        private readonly int $perPage,
        private readonly int $totalPages
    )
    {
        if ($currentPage < 1) {
            throw new InvalidArgumentException('Current page must be at least 1');
        }
        
        if ($perPage < 1) {
            throw new InvalidArgumentException('Per page must be at least 1');
        }
    }
    
    public function getEntities(): array
    {
        return $this->entities;
    }
    
    public function getTotal(): int
    {
        return $this->total;
    }
    
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }
    
    public function getPerPage(): int
    {
        return $this->perPage;
    }
    
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }
    
    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }
    
    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }
    
    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->currentPage + 1 : null;
    }
    
    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->currentPage - 1 : null;
    }
    
    public function getStartIndex(): int
    {
        return ($this->currentPage - 1) * $this->perPage + 1;
    }
    
    public function getEndIndex(): int
    {
        return min($this->currentPage * $this->perPage, $this->total);
    }
    
    public function isEmpty(): bool
    {
        return empty($this->entities);
    }
    
    public function toArray(): array
    {
        return [
            'entities' => $this->getEntities(),
            'meta' => [
                'total' => $this->getTotal(),
                'current_page' => $this->getCurrentPage(),
                'per_page' => $this->getPerPage(),
                'total_pages' => $this->getTotalPages(),
                'has_next_page' => $this->hasNextPage(),
                'has_previous_page' => $this->hasPreviousPage(),
            ]
        ];
    }
}