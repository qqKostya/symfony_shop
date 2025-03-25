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

    public function __construct(CartRepository $cartRepository, EntityManagerInterface $entityManager)
    {
        $this->cartRepository = $cartRepository;
        $this->entityManager  = $entityManager;
    }

    public function getCartByUser(?User $user): ?Cart
    {
        return $this->cartRepository->findOneBy(['user' => $user]);
    }

    public function addItemToCart(User $user, CartItemRequest $request): void
    {
        $cart    = $this->getCartByUser($user);
        $product = $this->entityManager->getRepository(Product::class)->find($request->productId);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->cartRepository->save($cart);
        }

        $existingItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $request->quantity);
            $this->entityManager->persist($existingItem);
        } else {
            $item = new CartItem();
            $item->setCart($cart);
            $item->setProduct($product);
            $item->setQuantity($request->quantity);
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    /**
     * @throws \Exception
     */
    public function removeItemFromCart(User $user, CartItemRequest $request): void
    {
        $cart         = $this->getCartByUser($user);
        $product      = $this->entityManager->getRepository(Product::class)->find($request->productId);
        $existingItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);

        if ($existingItem) {
            if (($existingItem->getQuantity() - $request->quantity) > 0) {
                $existingItem->setQuantity($existingItem->getQuantity() - $request->quantity);
                $this->entityManager->persist($existingItem);
            } else {
                $this->entityManager->remove($existingItem);
            }
            $this->entityManager->flush();
        } else {
            throw new \Exception('Товар не найден в корзине');
        }
    }

    public function deleteCart(User $user): void
    {
        $cart = $this->getCartByUser($user);
        $this->entityManager->remove($cart);
        $this->entityManager->flush();
    }

    public function getItemsFromCart(Cart $cart): array
    {
        return $this->entityManager->getRepository(CartItem::class)->findBy(['cart' => $cart]);
    }
}
