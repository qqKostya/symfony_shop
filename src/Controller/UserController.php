<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    private Serializer $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([new DateTimeNormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);
    }

    #[Route('/api/users', name: 'api_user_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findBy([], ['id' => 'ASC']);
        return new JsonResponse($this->serializer->normalize($users, null, ['groups' => 'user:read']), Response::HTTP_OK);
    }

    #[Route('/api/users/{id}', name: 'api_user_get', methods: ['GET'])]
    public function getUserById(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->serializer->normalize($user, null, ['groups' => 'user:read']), Response::HTTP_OK);
    }

    #[Route('/api/users', name: 'api_user_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['name']) || empty($data['email']) || empty($data['phone']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setPhone($data['phone']);
        $user->setEmail($data['email']);
        $user->setPasswordHash(password_hash($data['password'], PASSWORD_BCRYPT));

        $em->persist($user);
        $em->flush();

        return new JsonResponse($this->serializer->normalize($user, null, ['groups' => 'user:read']), Response::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', name: 'api_user_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!empty($data['name'])) {
            $user->setName($data['name']);
        }
        if (!empty($data['phone'])) {
            $user->setPhone($data['phone']);
        }
        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (!empty($data['password'])) {
            $user->setPasswordHash(password_hash($data['password'], PASSWORD_BCRYPT));
        }

        $user->setUpdatedAt(new \DateTime());

        $em->flush();

        return new JsonResponse($this->serializer->normalize($user, null, ['groups' => 'user:read']), Response::HTTP_OK);
    }


    #[Route('/api/users/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): Response
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
