<?php

declare(strict_types=1);

namespace App\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\CartItem;
use App\Cart\Repository\CartRepository;
use App\Cart\Request\CartItemRequest;
use App\Product\Entity\Product;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class CartService
{
    private CartRepository $cartRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        CartRepository $cartRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->cartRepository = $cartRepository;
        $this->entityManager = $entityManager;
    }

    public function getCartByUser(?User $user): ?Cart
    {
        return $this->cartRepository->findOneBy(['user' => $user]);
    }

    public function addItemToCart(User $user, CartItemRequest $request): void
    {
        $product = $this->entityManager->getRepository(Product::class)->find($request->productId);
        if (!$product) {
            throw new \InvalidArgumentException('Product not found');
        }

        $cart = $this->getCartByUser($user);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->entityManager->persist($cart);
        }

        $cart->addItem($product, $request->quantity);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();
    }

    public function removeItemFromCart(User $user, CartItemRequest $request): void
    {
        $cart = $this->getCartByUser($user);
        if (!$cart) {
            throw new \RuntimeException('Cart not found');
        }

        $product = $this->entityManager->getRepository(Product::class)->find($request->productId);
        if (!$product) {
            throw new \InvalidArgumentException('Product not found');
        }

        $cart->removeItem($product, $request->quantity);

        $this->entityManager->flush();
    }

    public function deleteCart(User $user): void
    {
        $cart = $this->getCartByUser($user);
        if ($cart) {
            $this->entityManager->remove($cart);
            $this->entityManager->flush();
        }
    }

    /**
     * @return CartItem[]
     */
    public function getItemsFromCart(Cart $cart): array
    {
        return $cart->getItems()->toArray();
    }
}
