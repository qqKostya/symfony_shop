<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Request\RequestRegister;
use App\User\Request\RequestUpdate;
use App\User\Serializer\SerializationGroups;
use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
final class UserController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private UserService $userService,
    ) {}

    #[Route('/users', methods: [Request::METHOD_GET])]
    public function list(): JsonResponse
    {
        $users = $this->userService->getAllUsers();

        return new JsonResponse($this->serializer->normalize($users, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }

    #[Route('/users/{id}', methods: [Request::METHOD_GET])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->normalize($user, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }

    #[Route('/register', methods: [Request::METHOD_POST])]
    public function create(
        #[MapRequestPayload]
        RequestRegister $request,
    ): JsonResponse {
        $user = $this->userService->createUser($request);

        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => SerializationGroups::USER_READ]);
    }

    #[Route('/users/{id}', methods: [Request::METHOD_PUT])]
    public function update(
        int $id,
        #[MapRequestPayload]
        RequestUpdate $request,
    ): JsonResponse {
        $user = $this->userService->updateUser($id, $request);

        if (!$user) {
            return new JsonResponse(['error' => 'Пользователь не найден'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->normalize($user, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }

    #[Route('/users/{id}', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $deleted = $this->userService->deleteUser($id);

        if (!$deleted) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
