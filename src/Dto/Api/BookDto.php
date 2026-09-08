<?php

namespace App\Dto\Api;

use DateTimeImmutable;

final class BookDto
{
    public int $id;
    public ?string $isbn;
    public ?string $title;
    public ?string $description;
    public ?float $price;
    public ?DateTimeImmutable $publicationDate;
    public array $authors = [];
    public array $images = [];
}