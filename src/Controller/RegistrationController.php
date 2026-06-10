<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}
    
    #[Route('/register', name: 'app.register')]
    public function add(Request $request): Response
    {
        $customer = new Customer;
        
        $form = $this->createForm(RegistrationFormType::class, $customer);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $this->passwordHasher->hashPassword($customer, $plainPassword);
            
            $customer->setPassword($hashedPassword);
            
            $this->entityManager->persist($customer);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Registration successful!');
            return $this->redirectToRoute('app.books.index');
        }
        
        return $this->render('register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
