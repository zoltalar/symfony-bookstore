<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Cart;
use App\Entity\Customer;
use App\Service\Cart as CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CartController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartService $cartService
    ) {}
    
    #[Route('/cart/index', name: 'app.cart.index')]
    public function index(Request $request): Response
    {
        $customer = $this->getUser();
        
        if ($customer) {
            $this->cartService->setCustomer($customer);
        }
        
        if ($request->isMethod('post')) {
            $action = strtolower($request->request->get('action'));
            
            if ($action === 'delete') {
                $id = intval($request->request->get('itemId'));
            
                $cart = $this
                    ->entityManager
                    ->getRepository(Cart::class)
                    ->find($id);

                if ($cart) {

                    if ($customer === $cart->getCustomer()) {
                        $this->entityManager->remove($cart);
                        $this->entityManager->flush();
                    }
                }
            } elseif ($action === 'update') {
                $quantities = $request->request->all('quantities');
                
                if (is_array($quantities)) {
                    
                    foreach ($quantities as $id => $quantity) {
                        $cart = $this
                            ->entityManager
                            ->getRepository(Cart::class)
                            ->find($id);
                        
                        if ($cart && $customer === $cart->getCustomer()) {
                            
                            if (intval($quantity) === 0) {
                                $this->entityManager->remove($cart);
                            } else {
                                $cart->setQuantity($quantity);
                            }
                            
                            $this->entityManager->flush();
                        }
                    }
                }
            }
            
            return $this->redirectToRoute('app.cart.index');
        }
        
        return $this->render('cart/index.html.twig', [
            'items' => $this->cartService->getCartItems(),
            'cart' => $this->cartService
        ]);
    }
    
    #[Route('/card/add/{id}', name: 'app.cart.add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, Book $book): Response
    {
        $customer = $this->getUser();
        
        if ($customer instanceof Customer) {
            $cart = $this
                ->entityManager
                ->getRepository(Cart::class)
                ->findOneBy([
                    'book' => $book,
                    'customer' => $customer
                ]);
            
            if ($cart) {
                $cart->setQuantity($cart->getQuantity() + 1);
                $this->entityManager->flush();
            } else {
                $cart = new Cart;
                $cart->setBook($book);
                $cart->setCustomer($customer);
                $cart->setQuantity(1);
                
                $this->entityManager->persist($cart);
                $this->entityManager->flush();
            }
        }
        
        return $this->redirectToRoute('app.cart.index');
    }
}
