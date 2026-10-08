<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\Enum\UserStatus;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdateUserDTO
{
    /**
     * @param list<string>|null $roles
     */
    public function __construct(
        public string $name,
        public ?string $email = null,
        public ?string $username = null,
        public ?string $identifierNumber = null,
        public ?string $password = null,
        public ?array $roles = null,
        public ?UserStatus $status = null,
        public ?string $phone = null,
        public ?string $avatarUrl = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $rawRoles = $request->request->all('roles');
        $roles = !empty($rawRoles) ? array_values(array_filter(array_map('strval', (array) $rawRoles))) : null;

        $rawStatus = $request->request->get('status');
        $status = $rawStatus ? UserStatus::tryFrom((string) $rawStatus) : null;

        $password = $request->request->get('password') ? (string) $request->request->get('password') : null;

        return new self(
            name: trim((string) $request->request->get('name', '')),
            email: $request->request->get('email') ? trim((string) $request->request->get('email')) : null,
            username: $request->request->get('username') ? trim((string) $request->request->get('username')) : null,
            identifierNumber: $request->request->get('identifier_number') ? trim((string) $request->request->get('identifier_number')) : null,
            password: $password !== '' ? $password : null,
            roles: $roles,
            status: $status,
            phone: $request->request->get('phone') ? trim((string) $request->request->get('phone')) : null,
            avatarUrl: $request->request->get('avatar_url') ? trim((string) $request->request->get('avatar_url')) : null,
        );
    }
}
