<?php

namespace App\Dto\Api;

use App\Entity\Book;
use DateTimeImmutable;

final class BookDto
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $isbn,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?DateTimeImmutable $publicationDate,
        public readonly ?float $price,
        public readonly array $authors,
        public readonly array $images
    ) {}
    
    public static function fromEntity(Book $book): self
    {
        $authors = $images = [];
        
        foreach ($book->getAuthors() as $author) {
            $authors[] = [
                'id' => $author->getId(),
                'name' => $author->getName()
            ];
        }
        
        foreach ($book->getImages() as $image) {
            $images[] = [
                'id' => $image->getId(),
                'uri' => $image->getUri()
            ];
        }
        
        return new self(
            $book->getId(),
            $book->getIsbn(),
            $book->getTitle(),
            $book->getDescription(),
            $book->getPublicationDate(),
            $book->getPrice(),
            $authors,
            $images
        );
    }
}