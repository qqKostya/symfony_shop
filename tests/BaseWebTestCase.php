<?php

declare(strict_types=1);

namespace App\Tests;

use App\User\Entity\Role;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    protected const ADMIN_EMAIL = 'admin@example.com';
    protected const ADMIN_PASSWORD = 'password123';
    protected const USER_EMAIL = 'usernormaltest@example.com';
    protected const USER_PASSWORD = 'userpass123';

    /**
     * Создание пользователя напрямую через EntityManager.
     */
    protected function createUserInDatabase(array $data, bool $isAdmin = false): User
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setPasswordHash(password_hash($data['password'], PASSWORD_BCRYPT));

        // Ищем, существует ли уже роль в базе данных
        $roleName = $isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER';
        $roleRepository = $entityManager->getRepository(Role::class);
        $role = $roleRepository->findOneBy(['name' => $roleName]);

        if (!$role) {
            $role = new Role($roleName);
            $entityManager->persist($role);
        }

        // Добавляем роль пользователю
        $user->addRole($role);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Создание или получение администратора.
     */
    protected function createAdminUser(): User
    {
        $repo = self::getContainer()->get('doctrine')->getRepository(User::class);

        $user = $repo->findOneBy(['email' => self::ADMIN_EMAIL]);

        if (!$user) {
            $user = $this->createUserInDatabase([
                'name' => 'Admin',
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
                'phone' => '0000000000',
            ], isAdmin: true);
        }

        return $user;
    }

    /**
     * Создание или получение обычного пользователя.
     */
    protected function createDefaultUser(): User
    {
        $repo = self::getContainer()->get('doctrine')->getRepository(User::class);

        $user = $repo->findOneBy(['email' => self::USER_EMAIL]);

        if (!$user) {
            $user = $this->createUserInDatabase([
                'name' => 'usernormaltest',
                'email' => self::USER_EMAIL,
                'password' => self::USER_PASSWORD,
                'phone' => '1231234567',
            ]);
        }

        return $user;
    }

    /**
     * Создание авторизованного клиента (по умолчанию админ).
     */
    protected function createAuthenticatedClient(bool $isAdmin = false): KernelBrowser
    {
        $client = static::createClient();

        if ($isAdmin) {
            $this->createAdminUser();
            $email = self::ADMIN_EMAIL;
            $password = self::ADMIN_PASSWORD;
        } else {
            $this->createDefaultUser();
            $email = self::USER_EMAIL;
            $password = self::USER_PASSWORD;
        }

        $client->jsonRequest('POST', '/api/login_check', [
            'email' => $email,
            'password' => $password,
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);
        $client->setServerParameter('HTTP_Authorization', \sprintf('Bearer %s', $data['token']));

        return $client;
    }
}
