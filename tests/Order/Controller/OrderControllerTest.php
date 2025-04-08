<?php

declare(strict_types=1);

namespace App\Tests\Order\Controller;

use App\Order\Entity\Enum\DeliveryType;
use App\Order\Entity\Enum\OrderStatus;
use App\Order\Entity\Order;
use App\Order\Entity\OrderItem;
use App\Product\Entity\Product;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class OrderControllerTest extends BaseWebTestCase
{
    private $client;

    private $user;

    private function createOrder($user, array $orderData): Order
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $order = new Order(
            $user,
            OrderStatus::PAID,
            $orderData['deliveryAddress'],
            $orderData['deliveryType'],
        );
        $entityManager->persist($order);

        foreach ($orderData['items'] as $itemData) {
            $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find($itemData['productId']);
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($itemData['quantity']);
            $entityManager->persist($orderItem);
        }

        $entityManager->flush();

        return $order;
    }

    private function createAuthenticatedClientForTest(): void
    {
        $this->client = $this->createAuthenticatedClient(true);
        $this->user = $this->getAuthenticatedUser();
    }

    public function testGetOrders(): void
    {
        $this->createAuthenticatedClientForTest();

        $orderData = [
            'deliveryAddress' => ['kladrId' => '12345', 'fullAddress' => 'Some Address'],
            'deliveryType' => DeliveryType::COURIER,
            'items' => [
                ['productId' => 1, 'quantity' => 2],
            ],
        ];
        $this->createOrder($this->user, $orderData);

        $this->client->jsonRequest('GET', '/api/orders');
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($responseData);
        self::assertArrayHasKey('id', $responseData[0]);
    }

    public function testGetOrder(): void
    {
        $this->createAuthenticatedClientForTest();

        $orderData = [
            'deliveryAddress' => ['kladrId' => '12345', 'fullAddress' => 'Some Address'],
            'deliveryType' => DeliveryType::COURIER,
            'items' => [
                ['productId' => 1, 'quantity' => 2],
            ],
        ];
        $order = $this->createOrder($this->user, $orderData);
        $orderId = $order->getId();

        $this->client->jsonRequest('GET', '/api/orders/' . $orderId);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEquals($orderId, $responseData['id']);
    }

    public function testCreateOrder(): void
    {
        $this->createAuthenticatedClientForTest();

        $orderData = [
            'userId' => $this->user->getId(),
            'status' => OrderStatus::PAID,
            'deliveryType' => DeliveryType::COURIER,
            'deliveryAddress' => [
                'kladrId' => 12345,
                'fullAddress' => 'Some Address',
            ],
            'items' => [
                ['productId' => 1, 'quantity' => 2],
            ],
        ];

        $this->client->jsonRequest('POST', '/api/orders', $orderData);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);


        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertArrayHasKey('order_id', $responseData);

        self::assertArrayHasKey('items', $responseData);
    }

    public function testUpdateOrderStatus(): void
    {
        $this->createAuthenticatedClientForTest();

        $orderData = [
            'deliveryAddress' => ['kladrId' => 12345, 'fullAddress' => 'Some Address'],
            'deliveryType' => DeliveryType::COURIER,
            'items' => [
                ['productId' => 1, 'quantity' => 2],
            ],
        ];
        $order = $this->createOrder($this->user, $orderData);
        $orderId = $order->getId();

        $statusData = [
            'userId' => $this->user->getId(),
            'orderId' => $orderId,
            'status' => 'в сборке',
        ];


        $this->client->jsonRequest('PATCH', '/api/admin/orders', $statusData);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertStringContainsString('в сборке', $responseData['message']);

        self::assertStringContainsString((string) $orderId, $responseData['message']);

        $updatedOrder = self::getContainer()->get('doctrine')->getRepository(Order::class)->find($orderId);
        self::assertEquals(OrderStatus::ASSEMBLING, $updatedOrder->getStatus());
    }
}
