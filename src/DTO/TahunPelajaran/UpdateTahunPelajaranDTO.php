<?php

declare(strict_types=1);

namespace App\DTO\TahunPelajaran;

use App\Enum\Semester;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdateTahunPelajaranDTO
{
    public function __construct(
        public string $tahun,
        public Semester $semester,
        public ?string $tanggalMulai = null,
        public ?string $tanggalSelesai = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        $tahun = trim($payload->getString('tahun', (string) $request->request->get('tahun', '')));
        $semesterStr = $payload->getString('semester', (string) $request->request->get('semester', Semester::GANJIL->value));
        $semester = Semester::tryFrom($semesterStr) ?? Semester::GANJIL;

        $tanggalMulai = $payload->has('tanggal_mulai') && $payload->getString('tanggal_mulai') !== ''
            ? $payload->getString('tanggal_mulai')
            : null;

        $tanggalSelesai = $payload->has('tanggal_selesai') && $payload->getString('tanggal_selesai') !== ''
            ? $payload->getString('tanggal_selesai')
            : null;

        return new self(
            tahun: $tahun,
            semester: $semester,
            tanggalMulai: $tanggalMulai,
            tanggalSelesai: $tanggalSelesai,
        );
    }

    /**
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->tahun === '') {
            $errors['tahun'] = 'Tahun pelajaran wajib diisi.';
        } elseif (!preg_match('/^\d{4}\/\d{4}$/', $this->tahun)) {
            $errors['tahun'] = 'Format tahun pelajaran harus YYYY/YYYY (contoh: 2026/2027).';
        }

        return $errors;
    }
}
