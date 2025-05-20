<?php

declare(strict_types=1);

namespace App\Order\Service;

use App\Order\Entity\Enum\DeliveryType;
use App\Order\Entity\Enum\OrderStatus;
use App\Order\Entity\Order;
use App\Order\Entity\OrderItem;
use App\Order\Repository\OrderRepository;
use App\Order\Request\CreateOrderRequest;
use App\Product\Entity\Product;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class OrderService
{
    private OrderRepository $orderRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(OrderRepository $orderRepository, EntityManagerInterface $entityManager)
    {
        $this->orderRepository = $orderRepository;
        $this->entityManager   = $entityManager;
    }

    public function getOrdersByUser(?User $user): array
    {
        return $this->orderRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'],
        );
    }

    public function getOrdersById(int $id, User $user): ?Order
    {
        return $this->orderRepository->findOneBy([
            'id'   => $id,
            'user' => $user,
        ]);
    }

    public function orderCreate(User $user, CreateOrderRequest $request): Order
    {
        $order = new Order(
            $user,
            OrderStatus::from($request->status),
            [
                'kladrId' => $request->deliveryAddress->kladrId,
                'fullAddress' => $request->deliveryAddress->fullAddress,
            ],
            DeliveryType::from($request->deliveryType),
        );

        $this->orderRepository->save($order);

        foreach ($request->items as $item) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $product = $this->entityManager->getRepository(Product::class)->find($item->productId);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($item->quantity);
            $this->entityManager->persist($orderItem);
        }

        $this->entityManager->flush();

        return $order;
    }

    public function getItemsFromOrder(Order $order): array
    {
        return $this->entityManager->getRepository(OrderItem::class)->findBy(['order' => $order]);
    }

    public function changeStatus(int $orderId, string $status): void
    {
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);

        if ($order === null) {
            throw new \RuntimeException('Order not found.');
        }

        $order->setStatus(OrderStatus::from($status));
        $this->orderRepository->save($order);
    }
}
