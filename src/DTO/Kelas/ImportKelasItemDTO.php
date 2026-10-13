<?php

declare(strict_types=1);

namespace App\DTO\Kelas;

final readonly class ImportKelasItemDTO
{
    public function __construct(
        public string $namaKelas,
        public int $tingkat,
        public ?string $jurusan = null,
        public int $kapasitas = 36,
        public ?string $waliKelas = null,
        public ?string $keterangan = null,
        public bool $isActive = true,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $namaKelas = trim((string) ($data['nama_kelas'] ?? $data['namaKelas'] ?? ''));
        $tingkat = (int) ($data['tingkat'] ?? 0);
        $jurusan = !empty($data['jurusan']) ? trim((string) $data['jurusan']) : null;
        $kapasitas = !empty($data['kapasitas']) ? (int) $data['kapasitas'] : 36;
        $waliKelas = !empty($data['wali_kelas']) ? trim((string) $data['wali_kelas']) : null;
        $keterangan = !empty($data['keterangan']) ? trim((string) $data['keterangan']) : null;
        $isActive = isset($data['is_active']) ? (bool) $data['is_active'] : true;

        return new self(
            namaKelas: $namaKelas,
            tingkat: $tingkat > 0 ? $tingkat : 10,
            jurusan: $jurusan,
            kapasitas: $kapasitas > 0 ? $kapasitas : 36,
            waliKelas: $waliKelas,
            keterangan: $keterangan,
            isActive: $isActive,
        );
    }
}
