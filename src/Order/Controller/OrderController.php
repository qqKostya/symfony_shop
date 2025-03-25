<?php

declare(strict_types=1);

namespace App\Order\Controller;

use App\Order\Request\CreateOrderRequest;
use App\Order\Request\StatusRequest;
use App\Order\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
final class OrderController extends AbstractController
{
    public function __construct(
        private OrderService $orderService,
        private SerializerInterface $serializer,
        private Security $security,
    ) {}

    #[Route('/orders', methods: [Request::METHOD_GET])]
    public function getOrders(): JsonResponse
    {
        $user   = $this->security->getUser();
        $orders = $this->orderService->getOrdersByUser($user);

        return new JsonResponse($this->serializer->normalize($orders, 'json'), Response::HTTP_OK);
    }

    #[Route('/orders/{id}', methods: [Request::METHOD_GET])]
    public function getOrder(int $id): JsonResponse
    {
        $user   = $this->security->getUser();
        $orders = $this->orderService->getOrdersById($id, $user);

        return new JsonResponse($this->serializer->normalize($orders, 'json'), Response::HTTP_OK);
    }

    #[Route('/orders', methods: [Request::METHOD_POST])]
    public function createOrder(
        #[MapRequestPayload]
        CreateOrderRequest $request,
    ): JsonResponse {
        $user       = $this->security->getUser();
        $order      = $this->orderService->orderCreate($user, $request);
        $orderItems = $this->orderService->getItemsFromOrder($order);

        return new JsonResponse($this->serializer->normalize([
            'order'  => $order,
            'items' => $orderItems,
        ], 'json'), Response::HTTP_OK);
    }

    #[Route('/orders/{id}/status', methods: [Request::METHOD_PATCH])]
    public function updateOrderStatus(
        #[MapRequestPayload]
        StatusRequest $request,
    ): JsonResponse {
        $order = $this->orderService->changeStatus($request->orderId, $request->status);

        return new JsonResponse($this->serializer->normalize($order, 'json'), Response::HTTP_OK);
    }
}
