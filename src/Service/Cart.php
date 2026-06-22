<?php

namespace App\Service;

use App\Entity\Customer;

final class Cart
{
    private const TAX_RATE = 0.23;
    private const SHIPPING_RATE = 8.99;
    
    private ?Customer $customer = null;
    
    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;
        
        return $this;
    }
    
    public function getBooks(): array
    {
        $books = [];
        
        if ($this->customer) {
            foreach ($this->customer->getCarts() as $cart) {
                $books[] = $cart->getBook();
            }
        }
        
        return $books;
    }
    
    public function getCartItems(): array
    {
        if ($this->customer) {
            return $this->customer->getCarts()->toArray();
        }
        
        return [];
    }
    
    public function getCartItemArray(): array
    {
        $array = [];
        $items = $this->getCartItems();
        
        foreach ($items as $item) {            
            $array[] = [
                'id' => $item->getId(),
                'quantity' => $item->getQuantity(),
                'book_subtotal' => $item->getBook()->getSubtotal($item->getQuantity())
            ];
        }
        
        return $array;
    }
    
    public function getSubtotal(): float
    {
        $subtotal = 0;
        
        if ($this->customer) {
            
            foreach ($this->customer->getCarts() as $cart) {
                $subtotal += ($cart->getQuantity() * $cart->getBook()->getPrice());
            }
        }
        
        return round($subtotal, 2);
    }
    
    public function getShipping(): float
    {
        return self::SHIPPING_RATE;
    }
    
    public function getTaxRate(): float
    {
        return self::TAX_RATE;
    }
    
    public function getTax(): float
    {
        return round(($this->getSubtotal() + $this->getShipping()) * $this->getTaxRate(), 2);
    }
    
    public function getTotal(): float
    {
        return round($this->getSubtotal() + $this->getShipping() + $this->getTax(), 2);
    }
    
    public function toArray(): array
    {
        return [
            'items' => $this->getCartItemArray(),
            'subtotal' => $this->getSubtotal(),
            'shipping' => $this->getShipping(),
            'tax' => $this->getTax(),
            'total' => $this->getTotal()
        ];
    }
}
