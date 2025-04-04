<?php

declare(strict_types=1);

namespace App\Tests\Cart\Controller;

use App\Cart\Entity\Cart;
use App\Product\Entity\Product;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CartControllerTest extends BaseWebTestCase
{
    //    public function createCartForUser($user): Cart
    //    {
    //        $entityManager = self::getContainer()->get('doctrine')->getManager();
    //
    //
    //        if (!$cart) {
    //            $cart = new Cart();
    //            $cart->setUser($user);
    //
    //            // Сохраняем корзину вручную
    //            $entityManager->persist($cart);
    //            $entityManager->flush();
    //        }
    //
    //        return $cart;
    //    }

    //    public function testGetCart(): void
    //    {
    //        // Получаем аутентифицированного клиента
    //        $client = $this->createAuthenticatedClient(true);
    //
    //        // Получаем текущего авторизованного пользователя
    //        $user = $this->getAuthenticatedUser();
    //        $userId = $user->getId(); // Получаем ID пользователя
    //
    //        // Запрашиваем корзину пользователя
    //        $client->jsonRequest('GET', '/api/cart');
    //        $responseData = json_decode($client->getResponse()->getContent(), true);
    //
    //        // Если корзины нет, создаем её для пользователя
    //        if ($client->getResponse()->getStatusCode() === Response::HTTP_NOT_FOUND) {
    //            $cart = $this->createCartForUser($user); // Создаем корзину, если её нет
    //            $client->jsonRequest('GET', '/api/cart');
    //            $responseData = json_decode($client->getResponse()->getContent(), true);
    //        }
    //
    //        // Проверка статуса ответа
    //        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    //        self::assertArrayHasKey('id', $responseData);
    //        self::assertEquals($userId, $responseData['user']['id']);
    //    }

    public function testAddItemToCart(): void
    {
        $client = $this->createAuthenticatedClient(true);

        // Генерация случайных данных для товара
        $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1); // предполагаем, что продукт с ID 1 существует

        $productData = [
            'productId' => $product->getId(),
            'quantity' => 1,
        ];

        // Добавляем товар в корзину
        $client->jsonRequest('POST', '/api/cart/add', $productData);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Проверка, что товар добавлен в корзину (например, корзина содержит хотя бы один товар)
        self::assertNotEmpty($responseData['items']);
    }

    //    public function testRemoveItemFromCart(): void
    //    {
    //        $client = $this->createAuthenticatedClient(true);
    //
    //        // Генерация случайных данных для товара
    //        $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1); // предполагаем, что продукт с ID 1 существует
    //
    //        $productData = [
    //            'productId' => $product->getId(),
    //            'quantity' => 1,
    //        ];
    //
    //        // Сначала добавляем товар в корзину
    //        $client->jsonRequest('POST', '/api/cart/add', $productData);
    //
    //        // Теперь удаляем товар из корзины
    //        $client->jsonRequest('POST', '/api/cart/remove', $productData);
    //        $responseData = json_decode($client->getResponse()->getContent(), true);
    //
    //        // Проверка статуса ответа
    //        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    //
    //        // Проверка, что корзина больше не содержит товаров
    //        self::assertEmpty($responseData['items']);
    //    }

    public function testClearCart(): void
    {
        $client = $this->createAuthenticatedClient(true);

        // Генерация случайных данных для товара
        $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1); // предполагаем, что продукт с ID 1 существует

        $productData = [
            'productId' => $product->getId(),
            'quantity' => 1,
        ];

        // Сначала добавляем товар в корзину
        $client->jsonRequest('POST', '/api/cart/add', $productData);

        // Теперь очищаем корзину
        $client->jsonRequest('DELETE', '/api/cart/clear');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Проверка, что корзина пуста (например, нет товаров в ответе)
        self::assertEmpty($responseData['items']);
    }
}
