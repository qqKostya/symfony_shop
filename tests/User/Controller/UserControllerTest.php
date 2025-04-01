<?php

declare(strict_types=1);

namespace App\Tests\User\Controller;

use App\User\Entity\Role;
use App\User\Entity\User;
use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface; // Добавляем этот use
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

final class UserControllerTest extends WebTestCase
{

//    private $client;
    private User $userAdmin;
//
//    protected function setUp(): void
//    {
//        parent::setUp();
//        // Инициализация клиента для тестов
//        $this->client = self::createClient();
//    }
//
//
////    public function testCreateUser(): void
////    {
////        // Создаем клиент для тестов
////        $client = self::createClient();
////
////        // Подготовка фейковых данных для запроса
////        $requestData = [
////            'name' => 'New User',
////            'phone' => '1234567890', // Телефон должен быть корректным
////            'email' => 'newuser@example.com', // Email должен быть корректным
////            'password' => 'password123', // Пароль должен быть не менее 8 символов
////        ];
////
////        // Выполняем POST-запрос к /api/register
////        $client->request(
////            'POST',
////            '/api/register',
////            [],
////            [],
////            ['CONTENT_TYPE' => 'application/json'],
////            json_encode($requestData)
////        );
////
////        // Проверка, что запрос выполнен успешно и пользователь создан
////        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
////
////        // Получаем тело ответа
////        $responseContent = $client->getResponse()->getContent();
////        $statusCode = $client->getResponse()->getStatusCode();
////        echo "Response Code: $statusCode\n";
////        echo "Response Content: $responseContent\n";
////
////        // Декодируем JSON-ответ
////        $jsonResponse = json_decode($responseContent, true);
////
////        // Проверяем, что в ответе есть email нового пользователя
////        $this->assertArrayHasKey('email', $jsonResponse);
////        $this->assertEquals($requestData['email'], $jsonResponse['email']);
////    }
//
//
//    public function testListUsers(): void
//    {
//        // Инициализация клиента
////        $this->client = self::createClient();
//
//        // Создаем администратора в тесте
//        $this->createAdminUser();
//
//        // Создаём JWT-токен для авторизации
//        $jwtClaims = [
//            'email' => $this->userAdmin->getEmail(),  // Email администратора
//            'roles' => ['ROLE_ADMIN']        // Роль администратора
//        ];
//
//        // Получаем авторизованный клиент
//        $authenticatedClient = self::createAuthenticatedClient($jwtClaims);
//
//        // Создаем уникальных пользователей для теста
//        $user1 = $this->createUser('User 1', self::generateRandomPhone(), self::generateRandomEmail(), 'password123');
//        $user2 = $this->createUser('User 2', self::generateRandomPhone(), self::generateRandomEmail(), 'password456');
//
//        // Сохраняем пользователей в базе данных
//        $entityManager = self::getContainer()->get('doctrine')->getManager();
//        $entityManager->persist($user1);
//        $entityManager->persist($user2);
//        $entityManager->flush();
//
//        // Выполняем GET-запрос на /api/admin/users с авторизованным клиентом
//        $authenticatedClient->request(
//            'GET',
//            '/api/admin/users',
//            [],
//            [],
//            ['CONTENT_TYPE' => 'application/json']
//        );
//
//        // Проверка, что код ответа 200 OK
//        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
//
//        // Декодируем тело ответа и проверяем, что оба пользователя присутствуют
//        $responseContent = $authenticatedClient->getResponse()->getContent();
//        $jsonResponse = json_decode($responseContent, true);
//
//        // Проверяем, что оба пользователя существуют в ответе
//        $this->assertCount(2, $jsonResponse); // Должно быть два пользователя
//
//        // Проверяем, что пользователи имеют правильные данные
//        $this->assertEquals('user1@example.com', $jsonResponse[0]['email']);
//        $this->assertEquals('user2@example.com', $jsonResponse[1]['email']);
//    }
//
//
//
//// Вспомогательная функция для создания пользователя
//    private function createUser(string $name, string $phone, string $email, string $password): User
//    {
//        $user = new User();
//        $user->setName($name);
//        $user->setPhone($phone);
//        $user->setEmail($email);
//        $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
//        return $user;
//    }
//
//
//
//    protected static function createAuthenticatedClient(array $claims)
//    {
//        $client = self::createClient();
//        $encoder = $client->getContainer()->get(JWTEncoderInterface::class);
//
//        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $encoder->encode($claims)));
//
//        return $client;
//    }
//
//    // Метод для создания администратора
    private function createAdminUser(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        // Проверяем, существует ли уже администратор с таким email
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);

        if ($existingUser) {
            // Если пользователь существует, используем его
            $this->userAdmin = $existingUser;
            return;
        }

        // Создаем нового пользователя с ролью ADMIN, если такого нет
        $this->userAdmin = new User();
        $this->userAdmin->setName('Admin');
        $this->userAdmin->setPhone('9999999999');
        $this->userAdmin->setEmail('admin@example.com');
        $this->userAdmin->setPasswordHash(password_hash('password123', PASSWORD_BCRYPT));

        // Ищем роль ROLE_ADMIN
        $roleRepo = $entityManager->getRepository(Role::class);  // Допустим, у вас есть сущность Role
        $roleAdmin = $roleRepo->findOneBy(['name' => 'ROLE_ADMIN']);

        // Если роль не найдена, создаем её
        if (!$roleAdmin) {
            $roleAdmin = new Role('ROLE_ADMIN');
            $entityManager->persist($roleAdmin);
            $entityManager->flush();
        }

        // Добавляем роль к пользователю
        $this->userAdmin->addRole($roleAdmin);

        // Сохраняем пользователя в базе данных
        $entityManager->persist($this->userAdmin);
        $entityManager->flush();
    }
//
//    private static function generateRandomPhone(): string
//    {
//        return  substr(strval(random_int(10000000, 99999999)), 0, 8);
//    }
//    private static function generateRandomEmail(): string
//    {
//
//    $number = substr(strval(random_int(10000000, 99999999)), 0, 8);
//        return  "user{$number}@example.com";
//    }

    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthenticatedClient(bool $isAdmin = false)
    {
        $client = static::createClient();
//        $this->createAdminUser();
        $client->jsonRequest(
            'POST',
            '/api/login_check',
            [
                'email' => 'admin@example.com',
                'password' => 'password123',
            ]
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    /**
     * test getPagesAction
     */
    public function testGetPages()
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest('GET', '/api/admin/users');
        var_dump($client->getResponse()->getContent());
    }
}
