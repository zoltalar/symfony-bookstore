<?php

namespace App\Controller\Api;

use App\Dto\Api\BookDto;
use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class BookController extends AbstractController
{
    public function __construct(
        private readonly BookRepository $bookRepository
    ) {}
    
    #[Route('/api/v2/books/index', name: 'app.api.books.index')]
    public function index(Request $request): Response
    {
        $keywords = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'b.price');
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 8);
        
        $books = $this
            ->bookRepository
            ->advancedSearch($keywords, $sort, $page, $limit);
        
        return $this->json($books->toArray());
    }
    
    #[Route('/api/v2/books/show/{id}', name: 'app.api.books.show')]
    public function show(Book $book): Response
    {
        $bookDto = BookDto::fromEntity($book);
        
        return $this->json($bookDto);
    }
}
