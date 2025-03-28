<?php

declare(strict_types=1);

namespace App\Cart\Controller;

use App\Cart\Request\CartItemRequest;
use App\Cart\Response\CartResponse;
use App\Cart\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api')]
final class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private Security $security,
        private TranslatorInterface $translator,
    ) {}

    #[Route('/cart', methods: [Request::METHOD_GET])]
    public function getCart(): JsonResponse
    {
        $user = $this->security->getUser();
        $cart = $this->cartService->getCartByUser($user);

        if ($cart === null) {
            return new JsonResponse(['error' => $this->translator->trans('cart.not_found')], Response::HTTP_NOT_FOUND);
        }
        $cartItems = $this->cartService->getItemsFromCart($cart);
        $cartInfoResponse = new CartResponse($cart->getId(), $cartItems);

        return new JsonResponse($cartInfoResponse, Response::HTTP_OK);
    }

    #[Route('/cart/add', methods: [Request::METHOD_POST])]
    public function addItem(
        #[MapRequestPayload]
        CartItemRequest $request,
    ): JsonResponse {
        $user = $this->security->getUser();
        $this->cartService->addItemToCart($user, $request);

        $cart      = $this->cartService->getCartByUser($user);
        $cartItems = $this->cartService->getItemsFromCart($cart);
        $cartInfoResponse = new CartResponse($cart->getId(), $cartItems);

        return new JsonResponse($cartInfoResponse, Response::HTTP_OK);

    }

    #[Route('/cart/remove', methods: [Request::METHOD_POST])]
    public function removeItem(
        #[MapRequestPayload]
        CartItemRequest $request,
    ): JsonResponse {
        $user = $this->security->getUser();
        $this->cartService->removeItemFromCart($user, $request);

        $cart      = $this->cartService->getCartByUser($user);
        $cartItems = $this->cartService->getItemsFromCart($cart);
        $cartInfoResponse = new CartResponse($cart->getId(), $cartItems);

        return new JsonResponse($cartInfoResponse, Response::HTTP_OK);

    }

    #[Route('/cart/clear', methods: [Request::METHOD_DELETE])]
    public function clearCart(): JsonResponse
    {
        $user = $this->security->getUser();
        $this->cartService->deleteCart($user);

        return new JsonResponse(['message' => $this->translator->trans('cart.cleared')], Response::HTTP_OK);
    }
}
