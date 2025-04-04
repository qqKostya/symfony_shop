<?php

declare(strict_types=1);

namespace App\Tests\Product\Controller;

use App\Product\Entity\Product;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ProductControllerTest extends BaseWebTestCase
{
    public function testListProducts(): void
    {
        $client = $this->createAuthenticatedClient(true);

        // Запрос на получение списка продуктов
        $client->jsonRequest('GET', '/api/products');

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка, что нет ошибок в ответе
        if (isset($responseData['error'])) {
            // Если есть ошибка, то выводим её
            self::fail('Error response: ' . json_encode($responseData['error']));
        }

        // Проверка, что ответ - это массив продуктов
        self::assertIsArray($responseData);
    }

    public function testCreateProduct(): void
    {
        $client = $this->createAuthenticatedClient(true);

        // Генерация случайных данных для продукта
        $data = $this->generateRandomProductData();

        // Создание продукта через новый метод
        $product = $this->createProduct($data);

        // Получение ID продукта
        $productId = $product->getId();

        // Запрос на получение созданного продукта
        $client->jsonRequest('GET', "/api/products/{$productId}");
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка, что продукт был успешно создан
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEquals($data['name'], $responseData['name']);
    }

    public function testGetProductById(): void
    {
        $client = $this->createAuthenticatedClient(true);

        // Генерация случайных данных для продукта
        $data = $this->generateRandomProductData();

        // Создание нового продукта
        $product = $this->createProduct($data);
        $productId = $product->getId();

        // Запрос на получение продукта по ID
        $client->jsonRequest('GET', "/api/products/{$productId}");
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEquals($productId, $responseData['id']);
    }

    public function testGetNonExistingProduct(): void
    {
        $client = $this->createAuthenticatedClient(true);

        // Запрос на несуществующий продукт
        $client->jsonRequest('GET', '/api/products/999999');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверка статуса ответа
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertArrayHasKey('error', $responseData);
    }

    public function testUpdateProduct(): void
    {
        $client = $this->createAuthenticatedClient(true);

        // Генерация случайных данных для продукта
        $data = $this->generateRandomProductData();

        // Создание нового продукта
        $product = $this->createProduct($data);
        $productId = $product->getId();

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
        self::assertEquals($updatedData['name'], $responseData['name']);
        self::assertEquals($updatedData['cost'], $responseData['cost']);
    }

    public function testDeleteProduct(): void
    {
        $client = $this->createAuthenticatedClient(true);

        // Генерация случайных данных для продукта
        $data = $this->generateRandomProductData();

        // Создание нового продукта
        $product = $this->createProduct($data);
        $productId = $product->getId();

        // Запрос на удаление продукта
        $client->jsonRequest('DELETE', "/api/products/{$productId}");
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Запрос на удаленный продукт
        $client->jsonRequest('GET', "/api/products/{$productId}");
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertArrayHasKey('error', $responseData);
    }

    /**
     * Метод для создания продукта через Entity Manager.
     *
     * @param array $data данные для создания продукта
     * @return Product сущность продукта
     */
    private function createProduct(array $data): Product
    {
        // Создание нового продукта
        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setCost($data['cost']);
        $product->setTax($data['tax']);
        $product->setWeight($data['weight']);
        $product->setHeight($data['height']);
        $product->setWidth($data['width']);
        $product->setLength($data['length']);

        // Получение Entity Manager через контейнер
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        return $product;
    }

    /**
     * Генерация случайных данных для продукта.
     */
    private function generateRandomProductData(): array
    {
        return [
            'name' => 'Product ' . random_int(1000, 9999),
            'description' => 'Description for product ' . random_int(1000, 9999),
            'cost' => random_int(10, 1000),
            'tax' => random_int(5, 200),
            'weight' => random_int(1, 50),
            'height' => random_int(10, 100),
            'width' => random_int(10, 100),
            'length' => random_int(10, 100),
        ];
    }
}
