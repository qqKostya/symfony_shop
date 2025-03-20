<?php

namespace App\Cart\Controller;

use App\Cart\DTO\AddItemDTO;
use App\Cart\Request\RequestItem;
use App\Cart\Serializer\SerializationGroups;
use App\Cart\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Cart\Entity\Cart;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/api', name: 'api_')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    private CartService $cartService;
    private SerializerInterface $serializer;
    private Security $security;
    public function __construct(CartService $cartService, SerializerInterface $serializer, Security $security)
    {
        $this->cartService = $cartService;
        $this->serializer = $serializer;
        $this->security = $security;
    }
    #[Route('/cart', name: 'cart_get', methods: [Request::METHOD_GET])]
    public function getCart(): JsonResponse
    {
        $user = $this->security->getUser();
        $cart = $this->cartService->getCartByUser($user);

        if (!$cart) {
            return new JsonResponse(['error' => 'Корзина не найдена'], Response::HTTP_NOT_FOUND);
        }

        $cartInfo = [
            'cart_id' => $cart->getId(),
            'items' => $this->cartService->getItemsFromCart($cart),
        ];


        return new JsonResponse($this->serializer->normalize($cartInfo, 'json', ['groups' => SerializationGroups::CART_ITEMS_READ]), Response::HTTP_OK);
    }


    #[Route('/cart/add', name: 'cart_add_item', methods: [Request::METHOD_POST])]
    public function addItem(
        #[MapRequestPayload]
         RequestItem $request
    ): JsonResponse
    {
        $user = $this->security->getUser();
        $this->cartService->addItemToCart($user, $request);

        $cart = $this->cartService->getCartByUser($user);
        $cartItems = $this->cartService->getItemsFromCart($cart);

        return new JsonResponse($this->serializer->normalize([
            'cart' => $cart,
            'items' => $cartItems
        ], 'json', ['groups' => SerializationGroups::CART_ITEMS_READ]), Response::HTTP_OK);

    }

    #[Route('/cart/remove', name: 'cart_remove_item', methods: [Request::METHOD_POST])]
    public function removeItem(
        #[MapRequestPayload]
        RequestItem $request,
    ): JsonResponse
    {
        $user = $this->security->getUser();
        $this->cartService->removeItemFromCart($user, $request);

        $cart = $this->cartService->getCartByUser($user);
        $cartItems = $this->cartService->getItemsFromCart($cart);

        return new JsonResponse($this->serializer->normalize([
            'cart' => $cart,
            'items' => $cartItems
        ], 'json', ['groups' => SerializationGroups::CART_ITEMS_READ]), Response::HTTP_OK);

    }

    #[Route('/cart/clear', name: 'cart_clear', methods: [Request::METHOD_DELETE])]
    public function clearCart(): JsonResponse
    {
        $user = $this->security->getUser();
        $this->cartService->deleteCart($user);

        return new JsonResponse(['message' => 'Корзина и все товары в ней удалены'], Response::HTTP_OK);
    }

}