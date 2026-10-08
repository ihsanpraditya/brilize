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
        $payload = $request->getPayload();

        $rawRoles = $payload->has('roles') ? $payload->all('roles') : ($request->request->has('roles') ? $request->request->all('roles') : null);
        $roles = $rawRoles !== null ? array_values(array_filter(array_map('strval', (array) $rawRoles))) : null;

        $rawStatus = $payload->has('status') ? $payload->getString('status') : ($request->request->has('status') ? (string) $request->request->get('status') : null);
        $status = $rawStatus ? UserStatus::tryFrom($rawStatus) : null;

        $password = $payload->has('password') ? $payload->getString('password') : ($request->request->has('password') ? (string) $request->request->get('password') : null);

        $name = $payload->getString('name', (string) $request->request->get('name', ''));
        $email = $payload->has('email') ? $payload->getString('email') : ($request->request->has('email') ? (string) $request->request->get('email') : null);
        $username = $payload->has('username') ? $payload->getString('username') : ($request->request->has('username') ? (string) $request->request->get('username') : null);
        $identifierNumber = $payload->has('identifier_number') ? $payload->getString('identifier_number') : ($request->request->has('identifier_number') ? (string) $request->request->get('identifier_number') : null);
        $phone = $payload->has('phone') ? $payload->getString('phone') : ($request->request->has('phone') ? (string) $request->request->get('phone') : null);
        $avatarUrl = $payload->has('avatar_url') ? $payload->getString('avatar_url') : ($request->request->has('avatar_url') ? (string) $request->request->get('avatar_url') : null);

        return new self(
            name: trim($name),
            email: $email !== null && $email !== '' ? trim($email) : null,
            username: $username !== null && $username !== '' ? trim($username) : null,
            identifierNumber: $identifierNumber !== null && $identifierNumber !== '' ? trim($identifierNumber) : null,
            password: $password !== null && $password !== '' ? $password : null,
            roles: $roles,
            status: $status,
            phone: $phone !== null && $phone !== '' ? trim($phone) : null,
            avatarUrl: $avatarUrl !== null && $avatarUrl !== '' ? trim($avatarUrl) : null,
        );
    }
}
