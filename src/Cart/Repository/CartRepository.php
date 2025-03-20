<?php

namespace App\Cart\Repository;

use App\Cart\Entity\Cart;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class CartRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Cart::class);
        $this->entityManager = $entityManager;
    }

    public function save(Cart $cart): void
    {
        $this->entityManager->persist($cart);
        $this->entityManager->flush();
    }

    public function delete(Cart $cart): void
    {
        $this->entityManager->remove($cart);
        $this->entityManager->flush();
    }
}
