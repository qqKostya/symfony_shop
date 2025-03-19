<?php

namespace App\User\Controller;

use App\User\Request\RequestRegister;
use App\User\Request\RequestUpdate;
use App\User\Entity\User;
use App\User\Serializer\SerializationGroups;
use App\User\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    private SerializerInterface $serializer;
    private JWTTokenManagerInterface $jwtManager;
    private UserService $userService;

    public function __construct(UserService $userService, SerializerInterface $serializer, JWTTokenManagerInterface $jwtManager)
    {
        $this->userService = $userService;
        $this->serializer = $serializer;
        $this->jwtManager = $jwtManager;
    }


    #[Route('/users', name: 'user_list', methods: [Request::METHOD_GET])]
    public function list(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        return new JsonResponse($this->serializer->normalize($users, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'user_get', methods: [Request::METHOD_GET])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->serializer->normalize($user, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }

    #[Route('/register', name: 'user_create', methods: [Request::METHOD_POST])]
    public function create(
        #[MapRequestPayload]
        RequestRegister $request
    ): JsonResponse
    {
        $user = $this->userService->createUser($request);
        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => SerializationGroups::USER_READ]);
    }

    #[Route('/users/{id}', name: 'user_update', methods: [Request::METHOD_PUT])]
    public function update(
        int                    $id,
        #[MapRequestPayload]
        RequestUpdate          $request): JsonResponse
    {
        $user = $this->userService->updateUser($id, $request);

        if (!$user) {
            return new JsonResponse(['error' => 'Пользователь не найден'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->normalize($user, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }


    #[Route('/users/{id}', name: 'user_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $deleted = $this->userService->deleteUser($id);

        if (!$deleted) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
