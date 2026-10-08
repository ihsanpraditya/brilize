<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use Symfony\Component\HttpFoundation\Request;

final readonly class LoginDTO
{
    public function __construct(
        public string $identifier,
        public string $password,
        public bool $remember = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            identifier: trim((string) $request->request->get('identifier', '')),
            password: (string) $request->request->get('password', ''),
            remember: (bool) $request->request->get('remember', false),
        );
    }

    /**
     * Validasi dasar DTO login
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->identifier === '') {
            $errors['identifier'] = 'NISN, NIP, Username, atau Email wajib diisi.';
        }

        if ($this->password === '') {
            $errors['password'] = 'Kata sandi wajib diisi.';
        }

        return $errors;
    }
}
