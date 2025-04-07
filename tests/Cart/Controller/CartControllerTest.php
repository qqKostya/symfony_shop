<?php

declare(strict_types=1);

namespace App\Tests\Cart\Controller;

use App\Cart\Entity\Cart;
use App\Cart\Entity\CartItem;
use App\Product\Entity\Product;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CartControllerTest extends BaseWebTestCase
{
    public function createCartForUser($user): Cart
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        // Проверяем, есть ли корзина у пользователя
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        // Если корзины нет, создаем её
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);

            // Сохраняем корзину
            $entityManager->persist($cart);
            $entityManager->flush();

            // Добавляем товар в корзину, например товар с ID 1
            $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1);

            if ($product) {
                $cartItem = new CartItem();
                $cartItem->setCart($cart);
                $cartItem->setProduct($product);
                $cartItem->setQuantity(2);

                $entityManager->persist($cartItem);
                $entityManager->flush();
            }
        }

        return $cart;
    }

    public function testGetCart(): void
    {
        // Получаем аутентифицированного клиента
        $client = $this->createAuthenticatedClient(true);

        // Получаем текущего авторизованного пользователя
        $user = $this->getAuthenticatedUser();
        $cart = $this->createCartForUser($user); // Создаем корзину, если её нет
        $cartId = $cart->getId(); // Получаем ID корзины
        $client->jsonRequest('GET', '/api/cart');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Проверка, что в ответе есть ID корзины и оно совпадает с полученным
        self::assertArrayHasKey('cart_id', $responseData);
        self::assertEquals($cartId, $responseData['cart_id']);
    }

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

    public function testRemoveItemFromCart(): void
    {
        // Получаем аутентифицированного клиента
        $client = $this->createAuthenticatedClient(true);

        // Получаем текущего авторизованного пользователя
        $user = $this->getAuthenticatedUser();
        $userId = $user->getId(); // Получаем ID пользователя

        // Создаем корзину, если её нет
        $cart = $this->createCartForUser($user);

        // Добавляем товар в корзину (например, товар с ID 1)
        $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1);
        $productData = [
            'productId' => $product->getId(),
            'quantity' => 3, // Сначала добавляем 3 штуки товара
        ];

        // Добавляем товар в корзину
        $client->jsonRequest('POST', '/api/cart/add', $productData);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка, что товар был добавлен в корзину
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($responseData['items']);

        $quantity = $responseData['items'][0]['quantity'];

        // Получаем ID товара, добавленного в корзину
        $cartItemIdToModify = $responseData['items'][0]['product_id']; // Получаем ID товара в корзине

        // Уменьшаем количество товара в корзине на 1
        $productDataToModify = [
            'productId' => $cartItemIdToModify, // ID товара в корзине
            'quantity' => 1, // Уменьшаем количество на 1 (с 3 до 2)
        ];

        // Отправляем запрос на уменьшение количества товара
        $client->jsonRequest('POST', '/api/cart/remove', $productDataToModify);
        $responseDataAfterModify = json_decode($client->getResponse()->getContent(), true);

        // Проверка, что статус ответа HTTP_OK
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Проверка, что количество товара в корзине уменьшилось на 1
        self::assertEquals($quantity - 1, $responseDataAfterModify['items'][0]['quantity']); // Проверяем, что теперь количество товара равно 2
    }

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
