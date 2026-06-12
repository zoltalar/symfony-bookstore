<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Image;
use App\Form\BookEditType;
use App\Form\BookType;
use App\Service\ImageHandler;
use App\Service\NameParser;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        $book = new Book;
        
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
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
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Book $book): Response
    {        
        $form = $this->createForm(BookEditType::class, $book, [
            'selected_authors' => $book->getAuthors()->toArray()
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            
            if ($form->get('addNewAuthor')->isClicked()) {
                $newAuthorName = $form->get('newAuthorName')->getData();
                list($firstName, $lastName) = (new NameParser($newAuthorName))->parse();
                
                $author = $this
                    ->entityManager
                    ->getRepository(Author::class)
                    ->findOneBy([
                        'firstName' => $firstName,
                        'lastName' => $lastName
                    ]);
                
                if (! $author) {
                    $author = new Author;
                    $author->setFirstName($firstName);
                    $author->setLastName($lastName);
                    
                    $this->entityManager->persist($author);
                    $this->entityManager->flush();
                    
                    $this->addFlash('success', sprintf('%s successfully added to the list of authors!', $author->getName()));
                }
                
                return $this->redirectToRoute('app.books.edit', ['id' => $book->getId()]);
            } elseif ($form->isValid()) {
                $uploadImages = $form->get('uploadImages')->getData();
                $authors = $form->get('authors')->getData();
            
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
                
                foreach ($book->getAuthors() as $author) {
                    $book->removeAuthor($author);
                }
                
                foreach ($authors as $author) {
                    $book->addAuthor($author);
                }

                $this->entityManager->flush();
                $this->addFlash('success', 'Book updated successfully!');
            }
            
            return $this->redirectToRoute('app.books.index');
        }
        
        return $this->render('book/edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book
        ]);
    }
}
