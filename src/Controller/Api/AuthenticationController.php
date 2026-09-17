<?php

namespace App\Controller\Api;

use App\Repository\CustomerRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthenticationController extends AbstractController
{
    #[Route(
        '/api/v2/authentication/login',
        name: 'app.api.authentication.login',
        methods: ['POST']
    )]
    public function login(
        Request $request,
        CustomerRepository $customerRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        
        if ($email && $password) {
            $customer = $customerRepository->findOneBy(['email' => $email]);
            
            if ($customer && $passwordHasher->isPasswordValid($customer, $password)) {
                return $this->json([
                    'access_token' => $jwtManager->create($customer)
                ]);
            }
        }
        
        return $this->json(['error' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
    }
}
