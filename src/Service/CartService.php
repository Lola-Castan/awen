<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $requestStack;
    private $entityManager;
    private const CART_SESSION_KEY = 'cart';

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    public function getCart(): array
    {
        $cart = $this->requestStack->getSession()->get(self::CART_SESSION_KEY, []);
        $productRepository = $this->entityManager->getRepository(Product::class);
        
        // Recharger les produits depuis la base de données
        foreach ($cart as $productId => $item) {
            if (isset($item['product'])) {
                $product = $productRepository->find($productId);
                if ($product) {
                    $cart[$productId]['product'] = $product;
                }
            }
        }
        
        return $cart;
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $productId = $product->getId();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product' => $product,
                'quantity' => $quantity
            ];
        }

        $this->saveCart($cart);
    }

    public function remove(Product $product): void
    {
        $cart = $this->getCart();
        $productId = $product->getId();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->saveCart($cart);
        }
    }

    public function updateQuantity(Product $product, int $quantity): void
    {
        $cart = $this->getCart();
        $productId = $product->getId();

        if (isset($cart[$productId])) {
            if ($quantity <= 0) {
                $this->remove($product);
            } else {
                $cart[$productId]['quantity'] = $quantity;
                $this->saveCart($cart);
            }
        }
    }

    public function clear(): void
    {
        $this->saveCart([]);
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }

    public function getTotalItems(): int
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }

    private function saveCart(array $cart): void
    {
        $this->requestStack->getSession()->set(self::CART_SESSION_KEY, $cart);
    }
} 