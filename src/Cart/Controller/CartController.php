<?php

declare(strict_types=1);

namespace App\Cart\Controller;

use App\Cart\Request\RequestItem;
use App\Cart\Serializer\SerializationGroups;
use App\Cart\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
final class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private SerializerInterface $serializer,
        private Security $security,
    ) {}

    #[Route('/cart', methods: [Request::METHOD_GET])]
    public function getCart(): JsonResponse
    {
        $user = $this->security->getUser();
        $cart = $this->cartService->getCartByUser($user);

        if ($cart === null) {
            return new JsonResponse(['error' => 'Корзина не найдена'], Response::HTTP_NOT_FOUND);
        }

        $cartInfo = [
            'cart_id' => $cart->getId(),
            'items'   => $this->cartService->getItemsFromCart($cart),
        ];


        return new JsonResponse($this->serializer->normalize($cartInfo, 'json', ['groups' => SerializationGroups::CART_ITEMS_READ]), Response::HTTP_OK);
    }

    #[Route('/cart/add', methods: [Request::METHOD_POST])]
    public function addItem(
        #[MapRequestPayload]
        RequestItem $request,
    ): JsonResponse {
        $user = $this->security->getUser();
        $this->cartService->addItemToCart($user, $request);

        $cart      = $this->cartService->getCartByUser($user);
        $cartItems = $this->cartService->getItemsFromCart($cart);

        return new JsonResponse($this->serializer->normalize([
            'cart'  => $cart,
            'items' => $cartItems,
        ], 'json', ['groups' => SerializationGroups::CART_ITEMS_READ]), Response::HTTP_OK);

    }

    #[Route('/cart/remove', methods: [Request::METHOD_POST])]
    public function removeItem(
        #[MapRequestPayload]
        RequestItem $request,
    ): JsonResponse {
        $user = $this->security->getUser();
        $this->cartService->removeItemFromCart($user, $request);

        $cart      = $this->cartService->getCartByUser($user);
        $cartItems = $this->cartService->getItemsFromCart($cart);

        return new JsonResponse($this->serializer->normalize([
            'cart'  => $cart,
            'items' => $cartItems,
        ], 'json', ['groups' => SerializationGroups::CART_ITEMS_READ]), Response::HTTP_OK);

    }

    #[Route('/cart/clear', methods: [Request::METHOD_DELETE])]
    public function clearCart(): JsonResponse
    {
        $user = $this->security->getUser();
        $this->cartService->deleteCart($user);

        return new JsonResponse(['message' => 'Корзина и все товары в ней удалены'], Response::HTTP_OK);
    }
}
