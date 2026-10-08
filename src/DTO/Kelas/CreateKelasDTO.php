<?php

declare(strict_types=1);

namespace App\DTO\Kelas;

use Symfony\Component\HttpFoundation\Request;

final readonly class CreateKelasDTO
{
    public function __construct(
        public string $namaKelas,
        public int $tingkat,
        public ?string $jurusan = null,
        public ?int $tahunPelajaranId = null,
        public ?int $waliKelasId = null,
        public int $kapasitas = 36,
        public ?string $keterangan = null,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        $namaKelas = trim($payload->getString('nama_kelas', (string) $request->request->get('nama_kelas', '')));
        $tingkat = (int) $payload->get('tingkat', $request->request->get('tingkat', 10));
        $jurusan = $payload->has('jurusan') && $payload->getString('jurusan') !== '' ? trim($payload->getString('jurusan')) : null;
        
        $tahunPelajaranId = $payload->has('tahun_pelajaran_id') && $payload->get('tahun_pelajaran_id')
            ? (int) $payload->get('tahun_pelajaran_id')
            : null;

        $waliKelasId = $payload->has('wali_kelas_id') && $payload->get('wali_kelas_id')
            ? (int) $payload->get('wali_kelas_id')
            : null;

        $kapasitas = (int) $payload->get('kapasitas', $request->request->get('kapasitas', 36));
        $keterangan = $payload->has('keterangan') && $payload->getString('keterangan') !== '' ? trim($payload->getString('keterangan')) : null;
        $isActive = $payload->getBoolean('is_active', (bool) $request->request->get('is_active', true));

        return new self(
            namaKelas: $namaKelas,
            tingkat: $tingkat,
            jurusan: $jurusan,
            tahunPelajaranId: $tahunPelajaranId,
            waliKelasId: $waliKelasId,
            kapasitas: $kapasitas > 0 ? $kapasitas : 36,
            keterangan: $keterangan,
            isActive: $isActive,
        );
    }

    /**
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->namaKelas === '') {
            $errors['nama_kelas'] = 'Nama kelas / rombel wajib diisi (contoh: X-RPL-1).';
        }

        if ($this->tingkat <= 0) {
            $errors['tingkat'] = 'Tingkat kelas harus berupa angka positif.';
        }

        return $errors;
    }
}
