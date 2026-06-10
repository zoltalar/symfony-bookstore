<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Image;
use App\Form\BookEditFormType;
use App\Form\BookFormType;
use App\Service\ImageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ImageHandler $imageHandler
    ) {}
    
    #[Route('/books/index', name: 'app.books.index')]
    public function index(Request $request): Response
    {
        $keywords = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'b.price');
        
        $books = $this
            ->entityManager
            ->getRepository(Book::class)
            ->advancedSearch($keywords, $sort);
        
        return $this->render('book/index.html.twig', compact('books'));
    }
    
    #[Route('/books/add', name: 'app.books.add')]
    public function add(Request $request): Response
    {
        $book = new Book;
        
        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Book created successfully!');
            return $this->redirectToRoute('app.books.index');
        }
        
        return $this->render('book/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/books/show/{slug}/{id}', name: 'app.books.show')]
    public function show(string $slug, Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book
        ]);
    }
    
    #[Route('/books/edit/{id}', name: 'app.books.edit')]
    public function edit(Request $request, Book $book): Response
    {
        $form = $this->createForm(BookEditFormType::class, $book);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadImages = $form->get('uploadImages')->getData();
            
            if ($uploadImages) {
                $files = is_array($uploadImages) ? $uploadImages : [$uploadImages];
                
                foreach ($files as $image) {
                    if ($image) {
                        try {
                            $uploadData = $this->imageHandler->upload($image);
                            
                            $image = new Image();
                            $image->setFile($uploadData['fileName']);
                            $image->setSize($uploadData['fileSize']);
                            $image->setMime($uploadData['mimeType']);
                            $image->setBook($book);
                            
                            $this->entityManager->persist($image);
                        } catch (Exception $e) {
                            $this->addFlash('error', $e->getMessage());
                        }
                    }
                }
            }
            
            $this->entityManager->flush();
            $this->addFlash('success', 'Book updated successfully!');
            
            return $this->redirectToRoute('app.books.index');
        }
        
        return $this->render('book/edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book
        ]);
    }
}
