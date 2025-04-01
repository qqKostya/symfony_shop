<?php

declare(strict_types=1);

namespace App\Tests\User\Controller;

use App\User\Entity\User;
use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface; // Добавляем этот use
use Symfony\Component\HttpFoundation\Response;

final class UserControllerTest extends WebTestCase
{
    private $userServiceMock;

    public function testCreateUser(): void
    {
        // Создаем мок для UserService
        $this->userServiceMock = $this->createMock(UserService::class);

        // Подготовка фейковых данных для запроса
        $requestData = [
            'name' => 'New User',
            'phone' => '1234567890', // Телефон должен быть корректным
            'email' => 'newuser@example.com', // Email должен быть корректным
            'password' => 'password123', // Пароль должен быть не менее 8 символов
        ];

        // Создаем объект User, который будет возвращен методом createUser
        $user = new User();
        $user->setName($requestData['name']);
        $user->setPhone($requestData['phone']);
        $user->setEmail($requestData['email']);
        $user->setPasswordHash($requestData['password']); // В реальной жизни следует хэшировать пароль

        // Подготовка мока для метода createUser
        $this->userServiceMock
            ->method('createUser')
            ->willReturn($user); // Возвращаем объект User вместо RegisterRequest

        // Создаем клиент для тестов
        $client = self::createClient();

        // Используем мок в сервисе
        $client->getContainer()->set(UserService::class, $this->userServiceMock);

        // Выполняем запрос с POST методом
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        // Проверка ответа на успешный запрос
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Получаем тело ответа
        $responseContent = $client->getResponse()->getContent();

        // Декодируем JSON-ответ
        $jsonResponse = json_decode($responseContent, true);

        // Проверка, что в ответе содержится email нового пользователя
        $this->assertArrayHasKey('email', $jsonResponse);
        $this->assertEquals($requestData['email'], $jsonResponse['email']);
    }

    public function testListUsers(): void
    {
        // Создаем мок для UserService
        $this->userServiceMock = $this->createMock(UserService::class);

        // Создаем несколько фейковых пользователей
        $user1 = new User();
        $user1->setName('User One');
        $user1->setEmail('user1@example.com');

        $user2 = new User();
        $user2->setName('User Two');
        $user2->setEmail('user2@example.com');

        // Подготавливаем метод getAllUsers, чтобы он возвращал массив с фейковыми пользователями
        $this->userServiceMock
            ->method('getAllUsers')
            ->willReturn([$user1, $user2]);

        // Создаем клиент для тестов
        $client = self::createClient();

        // Используем мок в сервисе
        $client->getContainer()->set(UserService::class, $this->userServiceMock);

        // Выполняем GET-запрос на /api/admin/users
        $client->request('GET', '/api/admin/users');

        // Проверка, что ответ успешный (HTTP 200 OK)
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Получаем тело ответа
        $responseContent = $client->getResponse()->getContent();

        // Декодируем JSON-ответ
        $jsonResponse = json_decode($responseContent, true);

        // Проверка, что в ответе содержится два пользователя
        $this->assertCount(2, $jsonResponse);

        // Проверка, что данные пользователей соответствуют фейковым данным
        $this->assertEquals('User One', $jsonResponse[0]['name']);
        $this->assertEquals('user1@example.com', $jsonResponse[0]['email']);

        $this->assertEquals('User Two', $jsonResponse[1]['name']);
        $this->assertEquals('user2@example.com', $jsonResponse[1]['email']);
    }
}
