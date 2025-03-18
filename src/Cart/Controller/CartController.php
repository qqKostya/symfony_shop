<?php

namespace App\Cart\Controller;

use App\Cart\DTO\AddItemDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Cart\Entity\Cart;
use App\Cart\Entity\CartItem;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart_get', methods: [Request::METHOD_GET])]
    public function getCart(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Требуеться авторизация'], Response::HTTP_UNAUTHORIZED);
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart) {
            return new JsonResponse(['error' => 'Корзина не найдена'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'cart_id' => $cart->getId(),
            'items' => $cart->getItems($em),
        ], Response::HTTP_OK);
    }

    #[Route('/cart/add', name: 'cart_add_item', methods: [Request::METHOD_POST])]
    public function addItem(
        #[MapRequestPayload]
        AddItemDTO $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Требуеться авторизация'], Response::HTTP_UNAUTHORIZED);
        }


        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]) ?? new Cart($user);
        $cart->addItem($em, $cart, $request->productId, $request->quantity);

        $em->persist($cart);
        $em->flush();

        return new JsonResponse(['message' => 'Товар добавлен в корзину'], Response::HTTP_CREATED);
    }

    #[Route('/cart/remove/{productId}', name: 'cart_remove_item', methods: [Request::METHOD_DELETE])]
    public function removeItem(
        #[MapRequestPayload]
        AddItemDTO $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Требуеться авторизация'], Response::HTTP_UNAUTHORIZED);
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart) {
            return new JsonResponse(['error' => 'Корзина не найдена'], Response::HTTP_NOT_FOUND);
        }

        $cart->removeItem($em, $cart, $request->productId, $request->quantity);

        return new JsonResponse(['error' => "Товар удален в количестве {$request->quantity}"], Response::HTTP_OK);
    }

    #[Route('/cart/clear', name: 'cart_clear', methods: [Request::METHOD_DELETE])]
    public function clearCart(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Требуеться авторизация'], Response::HTTP_UNAUTHORIZED);
        }

        // Найти корзину пользователя
        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart) {
            return new JsonResponse(['error' => 'Корзина не найдена'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($cart);

        $em->flush();

        return new JsonResponse(['message' => 'Корзина и все товары в ней удалены'], Response::HTTP_OK);
    }

}