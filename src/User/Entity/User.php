<?php

namespace App\User\Entity;

use App\User\Serializer\SerializationGroups;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
//#[ORM\Table(name: "users", schema: 'users')]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([SerializationGroups::USER_READ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Groups([SerializationGroups::USER_READ, SerializationGroups::USER_WRITE])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 20, unique: true)]
    #[Assert\NotBlank]
    #[Groups([SerializationGroups::USER_READ, SerializationGroups::USER_WRITE])]
    private string $phone;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups([SerializationGroups::USER_READ, SerializationGroups::USER_WRITE])]
    private string $email;

    //TODO: Length скорей всего не актуален так как мы храним хэш
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Пароль должен быть не менее {{ limit }} символов',
        maxMessage: 'Пароль должен быть не более {{ limit }} символов',
    )]
    #[Groups([SerializationGroups::USER_WRITE])]
    private string $passwordHash;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    #[Groups([SerializationGroups::USER_READ])]
    #[SerializedName('createdAt')]
    private \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "update")]
    #[Groups([SerializationGroups::USER_READ])]
    #[SerializedName('updatedAt')]
    private \DateTime $updatedAt;


    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['admin'];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }
}
