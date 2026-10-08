<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\DTO\User\UserResponseDTO;
use App\Entity\User;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use InvalidArgumentException;

final readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * @return list<UserResponseDTO>
     */
    public function getAllUsers(?string $query = null, ?UserStatus $status = null): array
    {
        $users = $this->userRepository->searchUsers($query, $status);

        return array_map(fn(User $user) => UserResponseDTO::fromEntity($user), $users);
    }

    public function getUserById(int $id): ?UserResponseDTO
    {
        $user = $this->userRepository->find($id);

        return $user !== null ? UserResponseDTO::fromEntity($user) : null;
    }

    public function createUser(CreateUserDTO $dto): UserResponseDTO
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        // Cek duplikasi email / username / identifierNumber
        if ($dto->email && $this->userRepository->findOneBy(['email' => strtolower($dto->email)])) {
            throw new InvalidArgumentException('Email sudah terdaftar pada sistem.');
        }

        if ($dto->username && $this->userRepository->findOneBy(['username' => strtolower($dto->username)])) {
            throw new InvalidArgumentException('Username sudah digunakan.');
        }

        if ($dto->identifierNumber && $this->userRepository->findOneBy(['identifierNumber' => $dto->identifierNumber])) {
            throw new InvalidArgumentException('Nomor NIP/NISN sudah terdaftar.');
        }

        $user = new User();
        $user->setName($dto->name);
        $user->setEmail($dto->email);
        $user->setUsername($dto->username);
        $user->setIdentifierNumber($dto->identifierNumber);
        $user->setPassword(password_hash($dto->password, PASSWORD_BCRYPT));
        $user->setRoles($dto->roles);
        $user->setStatus($dto->status);
        $user->setPhone($dto->phone);

        $this->userRepository->save($user, true);

        return UserResponseDTO::fromEntity($user);
    }

    public function updateUser(int $id, UpdateUserDTO $dto): UserResponseDTO
    {
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw new InvalidArgumentException('Data pengguna tidak ditemukan.');
        }

        $user->setName($dto->name);

        if ($dto->email !== null) {
            $existing = $this->userRepository->findOneBy(['email' => strtolower($dto->email)]);
            if ($existing && $existing->getId() !== $user->getId()) {
                throw new InvalidArgumentException('Email sudah digunakan oleh akun lain.');
            }
            $user->setEmail($dto->email);
        }

        if ($dto->username !== null) {
            $existing = $this->userRepository->findOneBy(['username' => strtolower($dto->username)]);
            if ($existing && $existing->getId() !== $user->getId()) {
                throw new InvalidArgumentException('Username sudah digunakan oleh akun lain.');
            }
            $user->setUsername($dto->username);
        }

        if ($dto->identifierNumber !== null) {
            $existing = $this->userRepository->findOneBy(['identifierNumber' => $dto->identifierNumber]);
            if ($existing && $existing->getId() !== $user->getId()) {
                throw new InvalidArgumentException('NIP/NISN sudah digunakan oleh akun lain.');
            }
            $user->setIdentifierNumber($dto->identifierNumber);
        }

        if ($dto->password !== null) {
            $user->setPassword(password_hash($dto->password, PASSWORD_BCRYPT));
        }

        if ($dto->roles !== null) {
            $user->setRoles($dto->roles);
        }

        if ($dto->status !== null) {
            $user->setStatus($dto->status);
        }

        if ($dto->phone !== null) {
            $user->setPhone($dto->phone);
        }

        if ($dto->avatarUrl !== null) {
            $user->setAvatarUrl($dto->avatarUrl);
        }

        $this->userRepository->save($user, true);

        return UserResponseDTO::fromEntity($user);
    }

    public function deleteUser(int $id): void
    {
        $user = $this->userRepository->find($id);
        if ($user !== null) {
            $this->userRepository->remove($user, true);
        }
    }
}
