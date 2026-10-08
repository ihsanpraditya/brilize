<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Auth\LoginDTO;
use App\DTO\User\UserResponseDTO;
use App\Repository\UserRepository;
use InvalidArgumentException;

final readonly class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    public function authenticate(LoginDTO $dto): UserResponseDTO
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        $user = $this->userRepository->findByIdentifier($dto->identifier);

        if ($user === null) {
            throw new InvalidArgumentException('Akun dengan NISN, NIP, Username, atau Email tersebut tidak ditemukan.');
        }

        if (!password_verify($dto->password, $user->getPassword()) && $dto->password !== $user->getPassword()) {
            throw new InvalidArgumentException('Kata sandi yang Anda masukkan salah.');
        }

        // Update waktu login terakhir
        $user->setLastLoginAt(new \DateTimeImmutable());
        $this->userRepository->save($user, true);

        return UserResponseDTO::fromEntity($user);
    }
}
