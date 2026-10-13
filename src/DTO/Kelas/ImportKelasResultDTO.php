<?php

declare(strict_types=1);

namespace App\DTO\Kelas;

final readonly class ImportKelasResultDTO
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
            $messages[] = sprintf('%d kelas baru berhasil ditambahkan', $this->totalCreated);
        }
        if ($this->totalUpdated > 0) {
            $messages[] = sprintf('%d kelas berhasil diperbarui', $this->totalUpdated);
        }
        if ($this->totalSkipped > 0) {
            $messages[] = sprintf('%d kelas dilewati (sudah ada)', $this->totalSkipped);
        }

        if (empty($messages)) {
            return 'Tidak ada data kelas yang diproses.';
        }

        return 'Import selesai: ' . implode(', ', $messages) . '.';
    }
}
