<?php

namespace App\Controller;

use App\Entity\Image;
use App\Service\ImageHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImageController extends AbstractController
{
    public function __construct(
        private ImageHandler $imageHandler
    ) {}
    
    #[Route('/images/delete/{id}', name: 'app.images.delete')]
    public function delete(Request $request, Image $image): Response
    {
        $this->imageHandler->delete($image);
        
        if ($request->query->has('return_uri')) {
            return $this->redirect($request->query->get('return_uri'));
        }
        
        return $this->redirectToRoute('app.books.index');
    }
}
