<?php

declare(strict_types=1);

namespace App\Tests\User\Controller;

use App\User\Entity\Role;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserControllerTest extends WebTestCase
{
    private User $admin;

    /**
     * Создание пользователя-администратора, если он ещё не существует.
     */
    private function createAdminUser(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $admin = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);

        if ($admin) {
            $this->admin = $admin;
            return;
        }

        $this->admin = new User();
        $this->admin->setName('Admin');
        $this->admin->setPhone('9999999999');
        $this->admin->setEmail('admin@example.com');
        $this->admin->setPasswordHash(password_hash('password123', PASSWORD_BCRYPT));

        $roleRepo = $entityManager->getRepository(Role::class);
        $roleAdmin = $roleRepo->findOneBy(['name' => 'ROLE_ADMIN']) ?? new Role('ROLE_ADMIN');
        if (!$roleAdmin->getId()) {
            $entityManager->persist($roleAdmin);
            $entityManager->flush();
        }

        $this->admin->addRole($roleAdmin);
        $entityManager->persist($this->admin);
        $entityManager->flush();
    }

    /**
     * Создаёт авторизованный клиент.
     */
    protected function createAuthenticatedClient(bool $isAdmin = false): KernelBrowser
    {
        $client = static::createClient();
        $this->createAdminUser();  // Создание или получение существующего администратора
        $client->jsonRequest('POST', '/api/login_check', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
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

    /**
     * Генерация случайного телефона.
     */
    private static function generateRandomPhone(): string
    {
        return substr(strval(random_int(1000000000, 9999999999)), 0, 10);
    }

    /**
     * Тест для получения списка пользователей.
     */
    public function testListUsers(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->jsonRequest('GET', '/api/admin/users');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Проверяем, что администратор присутствует в списке пользователей
        $adminUser = array_filter($responseData, fn($user) => $user['email'] === 'admin@example.com');
        $this->assertNotEmpty($adminUser);
        $this->assertContains('ROLE_ADMIN', reset($adminUser)['roles']);
    }

    /**
     * Тест для создания пользователя.
     */
    public function testCreateUser(): void
    {
        $client = static::createClient();
        $data = self::generateRandomData();

        $client->jsonRequest('POST', '/api/register', $data);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($data['email'], $responseData['email']);
        $this->assertArrayNotHasKey('password', $responseData);
    }

    /**
     * Тест для получения существующего пользователя.
     */
    public function testGetExistingUser(): void
    {
        $client = $this->createAuthenticatedClient();
        $data = self::generateRandomData();

        $client->jsonRequest('POST', '/api/register', $data);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $userId = $responseData['id'];

        $client->jsonRequest('GET', "/api/users/{$userId}");
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($userId, $data['id']);
    }

    /**
     * Тест для получения несуществующего пользователя.
     */
    public function testGetNonExistingUser(): void
    {
        $client = $this->createAuthenticatedClient();
        $translator = self::getContainer()->get(TranslatorInterface::class);

        $client->jsonRequest('GET', '/api/users/999999');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals($translator->trans('user.not_found'), $data['error']);
    }

    /**
     * Тест для обновления пользователя.
     */
    public function testUpdateUser(): void
    {
        $client = $this->createAuthenticatedClient();
        $data = self::generateRandomData();

        $client->jsonRequest('POST', '/api/register', $data);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $userId = $responseData['id'];

        $newData = self::generateRandomData();
        $name = $newData['name'];
        $phone = $newData['phone'];
        $updatedData = [
            'name' => $name,
            'phone' => $phone,
        ];

        $client->jsonRequest('PUT', "/api/users/{$userId}", $updatedData);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($name, $data['name']);
        $this->assertEquals($phone, $data['phone']);
    }

    /**
     * Тест для удаления пользователя.
     */
    public function testDeleteUser(): void
    {
        $client = $this->createAuthenticatedClient();
        $data = self::generateRandomData();

        $client->jsonRequest('POST', '/api/register', $data);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $userId = $responseData['id'];

        $client->jsonRequest('DELETE', "/api/users/{$userId}");
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Попробуем запросить удаленного пользователя
        $client->jsonRequest('GET', "/api/users/{$userId}");
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
