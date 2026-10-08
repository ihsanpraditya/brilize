<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\Entity\User;

final readonly class UserResponseDTO
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public ?string $username,
        public ?string $identifierNumber,
        public array $roles,
        public string $status,
        public string $statusLabel,
        public string $statusBadge,
        public ?string $phone,
        public ?string $avatarUrl,
        public ?string $lastLoginAt,
        public string $createdAt,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: (int) $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
            username: $user->getUsername(),
            identifierNumber: $user->getIdentifierNumber(),
            roles: $user->getRoles(),
            status: $user->getStatus()->value,
            statusLabel: $user->getStatus()->label(),
            statusBadge: $user->getStatus()->badgeClass(),
            phone: $user->getPhone(),
            avatarUrl: $user->getAvatarUrl(),
            lastLoginAt: $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
            createdAt: $user->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'identifierNumber' => $this->identifierNumber,
            'roles' => $this->roles,
            'status' => $this->status,
            'statusLabel' => $this->statusLabel,
            'statusBadge' => $this->statusBadge,
            'phone' => $this->phone,
            'avatarUrl' => $this->avatarUrl,
            'lastLoginAt' => $this->lastLoginAt,
            'createdAt' => $this->createdAt,
        ];
    }
}
