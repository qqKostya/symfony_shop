<?php

declare(strict_types=1);

namespace App\Product\Repository;

use App\Product\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class ProductRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Product::class);
        $this->entityManager = $entityManager;
    }

    public function findById(int $id): ?Product
    {
        return $this->find($id);
    }

    public function findAllProducts(): array
    {
        return $this->findBy([], ['id' => 'ASC']);
    }

    public function save(Product $product, bool $flush = true): void
    {
        $this->entityManager->persist($product);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function delete(Product $product, bool $flush = true): void
    {
        $this->entityManager->remove($product);
        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
