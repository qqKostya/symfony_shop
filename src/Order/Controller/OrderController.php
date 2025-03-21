<?php

namespace App\Order\Controller;

use App\Order\Request\RequestCreateOrder;
use App\Order\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class OrderController extends AbstractController
{
    private OrderService $orderService;
    private SerializerInterface $serializer;
    private Security $security;
    public function __construct(SerializerInterface $serializer, Security $security, OrderService $orderService)
    {
        $this->serializer = $serializer;
        $this->security = $security;
        $this->orderService = $orderService;
    }
    #[Route('/orders', name: 'cart_get', methods: [Request::METHOD_GET])]
    public function getOrders(): JsonResponse
    {
        $user = $this->security->getUser();
        $orders = $this->orderService->getOrdersByUser($user);
        return new JsonResponse($this->serializer->normalize($orders, 'json'), Response::HTTP_OK);
    }
    #[Route('/orders/{id}', name: 'cart_get', methods: [Request::METHOD_GET])]
    public function getOrder(int $id): JsonResponse
    {
        $orders = $this->orderService->getOrdersById($id);
        return new JsonResponse($this->serializer->normalize($orders, 'json'), Response::HTTP_OK);
    }
    #[Route('/orders', name: 'cart_get', methods: [Request::METHOD_POST])]
    public function createOrder(
        #[MapRequestPayload]
        RequestCreateOrder $request
    ): JsonResponse
    {
        $user = $this->security->getUser();
        $order = $this->orderService->orderCreate($user,$request);
        $orderItems = $this->orderService->getItemsFromOrder($order);
        return new JsonResponse($this->serializer->normalize([
            'cart' => $order,
            'items' => $orderItems
        ], 'json'), Response::HTTP_OK);
    }
}