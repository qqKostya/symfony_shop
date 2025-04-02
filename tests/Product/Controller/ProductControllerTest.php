<?php

declare(strict_types=1);

namespace App\Tests\Product\Controller;

use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class ProductControllerTest extends WebTestCase
{
    private const ADMIN_EMAIL = 'admin@example.com';
    private const ADMIN_PASSWORD = 'password123';

    private User $admin;

    // Создание администратора
    private function createAdminUser(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        // Проверка, существует ли администратор с таким email
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => self::ADMIN_EMAIL]);

        if ($existingUser) {
            $this->admin = $existingUser;
            return;
        }

        // Создание нового администратора
        $this->admin = new User();
        $this->admin->setName('Admin');
        $this->admin->setEmail(self::ADMIN_EMAIL);
        $this->admin->setPasswordHash(password_hash(self::ADMIN_PASSWORD, PASSWORD_BCRYPT));

        $entityManager->persist($this->admin);
        $entityManager->flush();
    }

    // Создание аутентифицированного клиента
    private function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $this->createAdminUser();

        // Запрос на получение токена
        $client->jsonRequest('POST', '/api/login_check', [
            'email' => self::ADMIN_EMAIL,
            'password' => self::ADMIN_PASSWORD,
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    public function testListProducts(): void
    {
        $client = $this->createAuthenticatedClient();

        // Запрос на получение списка продуктов
        $client->jsonRequest('GET', '/api/products');

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка, что ответ - это массив продуктов
        $this->assertIsArray($responseData);
    }

    public function testCreateProduct(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = [
            'name' => 'New Product',
            'description' => 'Product description',
            'cost' => 100,
            'tax' => 20,
            'weight' => 10,
            'height' => 20,
            'width' => 30,
            'length' => 40,
        ];

        // Отправка запроса на создание нового продукта
        $client->jsonRequest('POST', '/api/products', $data);

        // Получение ответа
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($data['name'], $responseData['name']);
    }

    public function testGetProductById(): void
    {
        $client = $this->createAuthenticatedClient();

        // Создание нового продукта
        $data = [
            'name' => 'Product for fetch',
            'description' => 'Product description',
            'cost' => 100,
            'tax' => 20,
        ];

        $client->jsonRequest('POST', '/api/products', $data);
        $product = json_decode($client->getResponse()->getContent(), true);

        $productId = $product['id'];

        // Запрос на получение продукта по ID
        $client->jsonRequest('GET', "/api/products/{$productId}");
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($productId, $responseData['id']);
    }

    public function testGetNonExistingProduct(): void
    {
        $client = $this->createAuthenticatedClient();

        // Запрос на несуществующий продукт
        $client->jsonRequest('GET', '/api/products/999999');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testUpdateProduct(): void
    {
        $client = $this->createAuthenticatedClient();

        // Создание нового продукта
        $data = [
            'name' => 'Product for update',
            'description' => 'Product description',
            'cost' => 100,
            'tax' => 20,
        ];

        $client->jsonRequest('POST', '/api/products', $data);
        $product = json_decode($client->getResponse()->getContent(), true);
        $productId = $product['id'];

        // Новые данные для обновления
        $updatedData = [
            'name' => 'Updated Product',
            'cost' => 150,
            'tax' => 30,
        ];

        // Отправка запроса на обновление продукта
        $client->jsonRequest('PUT', "/api/products/{$productId}", $updatedData);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($updatedData['name'], $responseData['name']);
        $this->assertEquals($updatedData['cost'], $responseData['cost']);
    }

    public function testDeleteProduct(): void
    {
        $client = $this->createAuthenticatedClient();

        // Создание нового продукта
        $data = [
            'name' => 'Product for deletion',
            'description' => 'Product description',
            'cost' => 100,
            'tax' => 20,
        ];

        $client->jsonRequest('POST', '/api/products', $data);
        $product = json_decode($client->getResponse()->getContent(), true);
        $productId = $product['id'];

        // Отправка запроса на удаление продукта
        $client->jsonRequest('DELETE', "/api/products/{$productId}");

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Запрос на удаленный продукт
        $client->jsonRequest('GET', "/api/products/{$productId}");
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка, что продукт больше не существует
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertArrayHasKey('error', $responseData);
    }
}
