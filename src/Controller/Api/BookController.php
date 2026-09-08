<?php

namespace App\Controller\Api;

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
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $offset = ($page - 1) * $limit;
        
        $books = $this
            ->bookRepository
            ->findBy(
                [],
                ['publicationDate' => 'DESC'],
                $limit,
                $offset
            );
        
        $total = $this->bookRepository->count();
        
        $bookDtos = array_map(function (Book $book) use ($request) {
            return $book->toDto($request->getSchemeAndHttpHost());
        }, $books);
        
        return $this->json([
            'data' => $bookDtos,
            'meta' => [
                'current_page' => $page,
                'limit' => $limit,
                'total' => $total
            ]
        ]);
    }
}
