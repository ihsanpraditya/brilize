<?php

declare(strict_types=1);

namespace App\DTO\Siswa;

final readonly class ImportSiswaResultDTO
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public int $totalProcessed,
        public int $totalCreated,
        public int $totalUpdated,
        public int $totalSkipped,
        public array $errors = [],
    ) {}

    public function getSummaryMessage(): string
    {
        $messages = [];
        if ($this->totalCreated > 0) {
            $messages[] = sprintf('%d siswa baru berhasil ditambahkan', $this->totalCreated);
        }
        if ($this->totalUpdated > 0) {
            $messages[] = sprintf('%d siswa berhasil diperbarui', $this->totalUpdated);
        }
        if ($this->totalSkipped > 0) {
            $messages[] = sprintf('%d siswa dilewati (sudah terdaftar)', $this->totalSkipped);
        }

        if (empty($messages)) {
            return 'Tidak ada data siswa yang diproses.';
        }

        return 'Import siswa selesai: ' . implode(', ', $messages) . '.';
    }
}
