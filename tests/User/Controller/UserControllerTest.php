<?php

declare(strict_types=1);

namespace App\Tests\User\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class UserControllerTest extends WebTestCase
{
    private string $jwtToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Получаем токен через /api/login_check
        $client = self::createClient();
        $client->request('POST', '/api/login_check', [
            'json' => [
                'email' => '',  // Убедись, что у тебя есть такой пользователь в базе
                'password' => '',   // Заменить на актуальный пароль
            ]
        ], [], ['CONTENT_TYPE' => 'application/json']);

        // Проверка, что токен существует
        $data = json_decode($client->getResponse()->getContent(), true);
        if (!isset($data['token'])) {
            $this->fail('JWT Token not found in the response');
        }
        $this->jwtToken = $data['token']; // Извлекаем токен
    }

    public function testListUsers(): void
    {
        if ($this->jwtToken === '') {
            $this->fail('JWT Token is empty');
        }

        $client = self::createClient();

        // Добавляем JWT токен в заголовки запроса
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $this->jwtToken);
        $client->request('GET', '/api/admin/users');

        // Проверка статуса и содержания
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJson($client->getResponse()->getContent());
    }

//    public function testGetUserById(): void
//    {
//        $client = self::createClient();
//        $client->request('GET', '/api/users/1');
//
//        if ($client->getResponse()->getStatusCode() === Response::HTTP_NOT_FOUND) {
//            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
//        } else {
//            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
//            self::assertJson($client->getResponse()->getContent());
//        }
//    }
//
//    public function testRegisterUser(): void
//    {
//        $client = self::createClient();
//        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
//            'name' => 'Test User',
//            'phone' => '1234567890',
//            'email' => 'test@example.com',
//            'password' => 'securePassword123',
//        ]));
//
//        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
//        self::assertJson($client->getResponse()->getContent());
//    }
//
//    public function testUpdateUser(): void
//    {
//        $client = self::createClient();
//        $client->request('PUT', '/api/users/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
//            'name' => 'Updated Name',
//            'phone' => '9876543210',
//        ]));
//
//        if ($client->getResponse()->getStatusCode() === Response::HTTP_NOT_FOUND) {
//            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
//        } else {
//            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
//            self::assertJson($client->getResponse()->getContent());
//        }
//    }
//
//    public function testDeleteUser(): void
//    {
//        $client = self::createClient();
//        $client->request('DELETE', '/api/users/1');
//
//        if ($client->getResponse()->getStatusCode() === Response::HTTP_NOT_FOUND) {
//            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
//        } else {
//            $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
//        }
//    }
}
