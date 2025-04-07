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

    private $product;

    // Метод для создания заказов
    private function createOrder($user, array $orderData): Order
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $order = new Order(
            $user,
            OrderStatus::PAID, // Статус по умолчанию
            $orderData['deliveryAddress'],
            $orderData['deliveryType'],
        );
        $entityManager->persist($order);

        // Добавляем товары в заказ
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

    // Метод для создания клиента и аутентификации
    private function createAuthenticatedClientForTest(): void
    {
        $this->client = $this->createAuthenticatedClient(true);
        $this->user = $this->getAuthenticatedUser();
    }

    public function testGetOrders(): void
    {
        // Создаем аутентифицированного клиента
        $this->createAuthenticatedClientForTest();

        // Создаем заказ для пользователя
        $orderData = [
            'deliveryAddress' => ['kladrId' => '12345', 'fullAddress' => 'Some Address'],
            'deliveryType' => DeliveryType::COURIER,
            'items' => [
                ['productId' => 1, 'quantity' => 2],
            ],
        ];
        $this->createOrder($this->user, $orderData);

        // Запрос на получение всех заказов пользователя
        $this->client->jsonRequest('GET', '/api/orders');
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // Проверка статуса и данных
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($responseData);
        self::assertArrayHasKey('id', $responseData[0]);
    }

    public function testGetOrder(): void
    {
        // Создаем аутентифицированного клиента
        $this->createAuthenticatedClientForTest();

        // Создаем заказ для пользователя
        $orderData = [
            'deliveryAddress' => ['kladrId' => '12345', 'fullAddress' => 'Some Address'],
            'deliveryType' => DeliveryType::COURIER,
            'items' => [
                ['productId' => 1, 'quantity' => 2],
            ],
        ];
        $order = $this->createOrder($this->user, $orderData);
        $orderId = $order->getId();

        // Запрос на получение заказа
        $this->client->jsonRequest('GET', '/api/orders/' . $orderId);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // Проверка статуса и данных
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEquals($orderId, $responseData['id']);
    }

    public function testCreateOrder(): void
    {
        // Создаем аутентифицированного клиента
        $this->createAuthenticatedClientForTest();

        // Создаем заказ для пользователя
        $orderData = [
            'userId' => $this->user->getId(),
            'status' => OrderStatus::PAID, // Статус для нового заказа
            'deliveryType' => DeliveryType::COURIER,
            'deliveryAddress' => [
                'kladrId' => 12345,
                'fullAddress' => 'Some Address',
            ],
            'items' => [
                ['productId' => 1, 'quantity' => 2], // Пример товара в заказе
            ],
        ];

        // Запрос на создание нового заказа
        $this->client->jsonRequest('POST', '/api/orders', $orderData);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);


        // Проверка, что статус ответа успешный
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Проверка, что ответ содержит идентификатор заказа
        self::assertArrayHasKey('order_id', $responseData);

        // Проверка, что в ответе есть товары
        self::assertArrayHasKey('items', $responseData);
    }

    public function testUpdateOrderStatus(): void
    {
        // Создаем аутентифицированного клиента
        $this->createAuthenticatedClientForTest();

        // Создаем заказ для пользователя
        $orderData = [
            'deliveryAddress' => ['kladrId' => 12345, 'fullAddress' => 'Some Address'],
            'deliveryType' => DeliveryType::COURIER,
            'items' => [
                ['productId' => 1, 'quantity' => 2],
            ],
        ];
        $order = $this->createOrder($this->user, $orderData);
        $orderId = $order->getId();

        // Данные для запроса на обновление статуса
        $statusData = [
            'userId' => $this->user->getId(),
            'orderId' => $orderId,
            'status' => 'в сборке',
        ];


        // Отправляем PATCH запрос для обновления статуса заказа
        $this->client->jsonRequest('PATCH', '/api/admin/orders', $statusData);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // Проверка, что статус ответа успешный
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Проверка, что сообщение о статусе было обновлено
        self::assertStringContainsString('в сборке', $responseData['message']);

        // Проверка, что сообщение нужный id заказа
        self::assertStringContainsString((string) $orderId, $responseData['message']);

        // Проверка, что статус заказа был обновлен
        $updatedOrder = self::getContainer()->get('doctrine')->getRepository(Order::class)->find($orderId);
        self::assertEquals(OrderStatus::ASSEMBLING, $updatedOrder->getStatus());
    }
}
