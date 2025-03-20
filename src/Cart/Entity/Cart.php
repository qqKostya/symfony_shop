<?php

namespace App\Cart\Entity;

use App\Cart\Serializer\SerializationGroups;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: "carts", schema: 'carts')]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([SerializationGroups::CART_READ, SerializationGroups::CART_ITEMS_READ])]
    private int $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    #[Groups([SerializationGroups::CART_READ, SerializationGroups::CART_WRITE])]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    #[SerializedName('createdAt')]
    #[Groups([SerializationGroups::CART_READ])]
    private \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "update")]
    #[SerializedName('updatedAt')]
    #[Groups([SerializationGroups::CART_READ])]
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
}
