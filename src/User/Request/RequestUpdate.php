<?php

declare(strict_types=1);

namespace App\User\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RequestUpdate
{
    #[Assert\Length(min: 2, max: 255, minMessage: 'Имя должно содержать минимум {{ limit }} символа')]
    public ?string $name;

    #[Assert\Length(min: 10, max: 20, minMessage: 'Некорректный номер телефона')]
    public ?string $phone;

    #[Assert\Email(message: 'Некорректный email')]
    public ?string $email;

    #[Assert\Length(
        min: 8,
        minMessage: 'Пароль должен быть не менее {{ limit }} символов',
    )]
    public ?string $password;

    public function __construct(?string $name = null, ?string $phone = null, ?string $email = null, ?string $password = null)
    {
        $this->name     = $name;
        $this->phone    = $phone;
        $this->email    = $email;
        $this->password = $password;
    }
}
