<?php

declare(strict_types=1);

namespace App\Order\Controller;

use App\Order\Request\CreateOrderRequest;
use App\Order\Request\StatusRequest;
use App\Order\Response\OrderResponse;
use App\Order\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api')]
final class OrderController extends AbstractController
{
    public function __construct(
        private OrderService $orderService,
        private SerializerInterface $serializer,
        private Security $security,
        private TranslatorInterface $translator,
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
        $order = $this->orderService->getOrdersById($id, $user);

        if ($order === null) {
            throw new NotFoundHttpException($this->translator->trans('order.not_found'));
        }

        return new JsonResponse($this->serializer->normalize($order, 'json'), Response::HTTP_OK);
    }

    #[Route('/orders', methods: [Request::METHOD_POST])]
    public function createOrder(
        #[MapRequestPayload]
        CreateOrderRequest $request,
    ): JsonResponse {
        $user       = $this->security->getUser();
        $order      = $this->orderService->orderCreate($user, $request);
        $orderItems = $this->orderService->getItemsFromOrder($order);
        $orderResponse = new OrderResponse($order->getId(), $orderItems);

        return new JsonResponse($orderResponse, Response::HTTP_OK);
    }

    #[Route('/admin/orders', methods: [Request::METHOD_PATCH])]
    public function updateOrderStatus(
        #[MapRequestPayload]
        StatusRequest $request,
    ): JsonResponse {
        $this->orderService->changeStatus($request->orderId, $request->status);

        return new JsonResponse([
            'message' => $this->translator->trans('order.status_updated', ['%id%' => $request->orderId, '%status%' => $request->status]),
        ], Response::HTTP_OK);
    }
}
