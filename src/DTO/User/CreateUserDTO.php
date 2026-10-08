<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\Enum\UserRole;
use App\Enum\UserStatus;
use Symfony\Component\HttpFoundation\Request;

final readonly class CreateUserDTO
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public string $name,
        public ?string $email,
        public ?string $username,
        public ?string $identifierNumber,
        public string $password,
        public array $roles = [UserRole::SISWA->value],
        public UserStatus $status = UserStatus::ACTIVE,
        public ?string $phone = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $rawRoles = $request->request->all('roles');
        $roles = !empty($rawRoles) ? (array) $rawRoles : [UserRole::SISWA->value];

        $rawStatus = (string) $request->request->get('status', UserStatus::ACTIVE->value);
        $status = UserStatus::tryFrom($rawStatus) ?? UserStatus::ACTIVE;

        return new self(
            name: trim((string) $request->request->get('name', '')),
            email: $request->request->get('email') ? trim((string) $request->request->get('email')) : null,
            username: $request->request->get('username') ? trim((string) $request->request->get('username')) : null,
            identifierNumber: $request->request->get('identifier_number') ? trim((string) $request->request->get('identifier_number')) : null,
            password: (string) $request->request->get('password', ''),
            roles: array_values(array_filter(array_map('strval', $roles))),
            status: $status,
            phone: $request->request->get('phone') ? trim((string) $request->request->get('phone')) : null,
        );
    }

    /**
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->name === '') {
            $errors['name'] = 'Nama lengkap wajib diisi.';
        }

        if ($this->email === null && $this->username === null && $this->identifierNumber === null) {
            $errors['identifier'] = 'Setidaknya salah satu dari Email, Username, atau NIP/NISN harus diisi.';
        }

        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        if (strlen($this->password) < 6) {
            $errors['password'] = 'Kata sandi minimal harus 6 karakter.';
        }

        return $errors;
    }
}
