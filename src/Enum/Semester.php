<?php

declare(strict_types=1);

namespace App\Enum;

enum Semester: string
{
    case GANJIL = 'ganjil';
    case GENAP = 'genap';

    public function label(): string
    {
        return match ($this) {
            self::GANJIL => 'Ganjil',
            self::GENAP => 'Genap',
        };
    }
}
