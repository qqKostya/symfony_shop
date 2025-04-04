<?php

declare(strict_types=1);

namespace App\Tests\User\Controller;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserControllerTest extends BaseWebTestCase
{
    /**
     * Тест для получения списка пользователей.
     */
    public function testListUsers(): void
    {
        $client = $this->createAuthenticatedClient(true);

        $client->jsonRequest('GET', '/api/admin/users');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertIsArray($responseData);
        self::assertNotEmpty($responseData);

        $adminUser = array_filter($responseData, static fn($user) => $user['email'] === self::ADMIN_EMAIL);
        self::assertNotEmpty($adminUser);
        self::assertContains('ROLE_ADMIN', reset($adminUser)['roles']);
    }

    public function testCreateUser(): void
    {
        $client = self::createClient();
        $data = self::generateRandomData();

        $client->jsonRequest('POST', '/api/register', $data);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertArrayHasKey('id', $responseData);
        self::assertEquals($data['email'], $responseData['email']);
        self::assertArrayNotHasKey('password', $responseData);
    }

    public function testGetExistingUser(): void
    {
        $client = $this->createAuthenticatedClient(true);
        $data = self::generateRandomData();

        $user = $this->createUserInDatabase($data);
        $userId = $user->getId();

        $client->jsonRequest('GET', "/api/users/{$userId}");
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEquals($userId, $data['id']);
    }

    public function testGetNonExistingUser(): void
    {
        $client = $this->createAuthenticatedClient(true);
        $translator = self::getContainer()->get(TranslatorInterface::class);

        $client->jsonRequest('GET', '/api/users/999999');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertArrayHasKey('error', $data);
        self::assertEquals($translator->trans('user.not_found'), $data['error']);
    }

    public function testUpdateUser(): void
    {
        $client = $this->createAuthenticatedClient(true);
        $data = self::generateRandomData();

        $user = $this->createUserInDatabase($data);
        $userId = $user->getId();

        $newData = self::generateRandomData();
        $updatedData = [
            'name' => $newData['name'],
            'phone' => $newData['phone'],
        ];

        $client->jsonRequest('PUT', "/api/users/{$userId}", $updatedData);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEquals($updatedData['name'], $data['name']);
        self::assertEquals($updatedData['phone'], $data['phone']);
    }

    public function testDeleteUser(): void
    {
        $client = $this->createAuthenticatedClient(true);
        $data = self::generateRandomData();

        $client->jsonRequest('POST', '/api/register', $data);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $userId = $responseData['id'];

        $client->jsonRequest('DELETE', "/api/users/{$userId}");
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->jsonRequest('GET', "/api/users/{$userId}");
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Генерация случайных данных для пользователя.
     */
    private static function generateRandomData(): array
    {
        $phone = self::generateRandomPhone();

        return [
            'name' => "New User {$phone}",
            'email' => "user{$phone}@example.com",
            'password' => 'SecurePass123',
            'phone' => $phone,
        ];
    }

    private static function generateRandomPhone(): string
    {
        return substr((string) random_int(1000000000, 9999999999), 0, 10);
    }
}
