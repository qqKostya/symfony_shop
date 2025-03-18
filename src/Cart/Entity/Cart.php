<?php

namespace App\Cart\Entity;

use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: "carts", schema: 'carts')]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    #[SerializedName('createdAt')]
    private \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "update")]
    #[SerializedName('updatedAt')]
    private \DateTime $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Получить все товары в корзине
     */
    public function getItems(EntityManagerInterface $em): array
    {
        return $em->getRepository(CartItem::class)->findBy(['cart' => $this]);
    }

    /**
     * Добавить товар в корзину
     */
    public function addItem(EntityManagerInterface $em, Cart $cart, int $productId, int $quantity): void
    {
        $existingItem = $em->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'productId' => $productId]);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
            $em->persist($existingItem);
        } else {
            $item = new CartItem();
            $item->setProductId($productId);
            $item->setQuantity($quantity);
            $item->setCart($cart);

            $em->persist($item);
        }

        $em->flush();
    }

    /**
     * Удалить товар из корзины или уменьшить его количество
     */
    public function removeItem(EntityManagerInterface $em, Cart $cart, int $productId, int $quantity): void
    {
        $existingItem = $em->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'productId' => $productId]);

        if ($existingItem) {
            if (($existingItem->getQuantity() - $quantity) > 0) {
                $existingItem->setQuantity($existingItem->getQuantity() - $quantity);
                $em->persist($existingItem);
            } else {
                $em->remove($existingItem);
            }
        } else {
            throw new \Exception('Товар не найден');
        }

        $em->flush();
    }

}