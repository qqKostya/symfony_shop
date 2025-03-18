<?php

namespace App\User\Controller;

use App\User\DTO\RegisterDTO;
use App\User\DTO\UpdateDTO;
use App\User\Entity\User;
use App\User\Serializer\SerializationGroups;
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

    public function __construct(SerializerInterface $serializer, JWTTokenManagerInterface $jwtManager)
    {
        $this->serializer = $serializer;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/users', name: 'user_list', methods: [Request::METHOD_GET])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $users = $em->getRepository(User::class)->findBy([], ['id' => 'ASC']);
        return new JsonResponse($this->serializer->normalize($users, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'user_get', methods: [Request::METHOD_GET])]
    public function getUserById(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->serializer->normalize($user, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }

    #[Route('/register', name: 'user_create', methods: [Request::METHOD_POST])]
    public function create(
        #[MapRequestPayload]
        RegisterDTO $request, EntityManagerInterface $em
    ): JsonResponse
    {
        $user = new User();
        $user->setName($request->name);
        $user->setPhone($request->phone);
        $user->setEmail($request->email);
        $user->setPasswordHash(password_hash($request->password, PASSWORD_BCRYPT));

        $em->persist($user);
        $em->flush();

        return new JsonResponse($this->serializer->normalize($user, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'user_update', methods: [Request::METHOD_PUT])]
    public function update(
        int $id,
        #[MapRequestPayload]
        UpdateDTO $request,
        EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($request->name !== null && $request->name !== $user->getName()) {
            $user->setName($request->name);
        }
        if ($request->phone !== null && $request->phone !== $user->getPhone()) {
            $user->setPhone($request->phone);
        }
        if ($request->email !== null && $request->email !== $user->getEmail()) {
            $user->setEmail($request->email);
        }
        if ($request->password !== null && $request->password !== $user->getPasswordHash()) {
            $user->setPasswordHash(password_hash($request->password, PASSWORD_BCRYPT));
        }

        $user->setUpdatedAt(new \DateTime());

        $em->flush();

        return new JsonResponse($this->serializer->normalize($user, 'json', ['groups' => SerializationGroups::USER_READ]), Response::HTTP_OK);
    }


    #[Route('/users/{id}', name: 'user_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
