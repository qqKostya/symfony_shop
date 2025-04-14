<?php

declare(strict_types=1);

namespace App\Cart\Entity;

use App\Cart\Serializer\SerializationGroups;
use App\Product\Entity\Product;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Translation\Translator;

#[ORM\Entity]
#[ORM\Table(name: 'carts', schema: 'carts')]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([SerializationGroups::CART_READ, SerializationGroups::CART_ITEMS_READ])]
    private int $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups([SerializationGroups::CART_READ, SerializationGroups::CART_WRITE])]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[SerializedName('createdAt')]
    #[Groups([SerializationGroups::CART_READ])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    #[SerializedName('updatedAt')]
    #[Groups([SerializationGroups::CART_READ])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'cart', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->items = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Product $product, int $quantity): void
    {
        foreach ($this->items as $item) {
            if ($item->getProduct() === $product) {
                $item->increaseQuantity($quantity);

                return;
            }
        }

        $item = new CartItem($this, $product, $quantity);
        $this->items->add($item);
    }

    public function removeItem(Product $product, int $quantity): void
    {
        foreach ($this->items as $item) {
            if ($item->getProduct() === $product) {
                $item->decreaseQuantity($quantity);
                if ($item->getQuantity() <= 0) {
                    $this->items->removeElement($item);
                }

                return;
            }
        }

        throw new \DomainException(Translator::class->trans('cart.not_found'));
    }

    public function clearItems(): void
    {
        $this->items->clear();
    }
}
