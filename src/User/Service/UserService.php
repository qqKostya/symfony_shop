<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Request\RequestRegister;
use App\User\Request\RequestUpdate;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserService
{
    private UserRepository $userRepository;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAllUsers();
    }

    public function getUserById(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }

    public function createUser(RequestRegister $request): User
    {
        $user = new User();
        $user->setName($request->name);
        $user->setPhone($request->phone);
        $user->setEmail($request->email);
        $user->setPasswordHash($this->passwordHasher->hashPassword($user, $request->password));

        $this->userRepository->save($user);

        return $user;
    }

    public function updateUser(int $id, RequestUpdate $request): ?User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return null;
        }

        if ($request->name !== null) {
            $user->setName($request->name);
        }
        if ($request->phone !== null) {
            $user->setPhone($request->phone);
        }
        if ($request->email !== null) {
            $user->setEmail($request->email);
        }
        if ($request->password !== null) {
            $user->setPasswordHash($this->passwordHasher->hashPassword($user, $request->password));
        }

        $this->userRepository->save($user);

        return $user;
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return false;
        }

        $this->userRepository->delete($user);

        return true;
    }
}
