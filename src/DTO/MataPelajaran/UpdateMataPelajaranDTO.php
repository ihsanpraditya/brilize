<?php

declare(strict_types=1);

namespace App\DTO\MataPelajaran;

use App\Enum\KelompokMapel;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdateMataPelajaranDTO
{
    /**
     * @param int[] $guruPengampuIds
     */
    public function __construct(
        public string $kodeMapel,
        public string $namaMapel,
        public KelompokMapel $kelompok,
        public ?int $tingkat = null,
        public ?string $jurusan = null,
        public int $kkm = 75,
        public int $bebanJamPerMinggu = 2,
        public array $guruPengampuIds = [],
        public ?int $guruKoordinatorId = null,
        public int $urutan = 0,
        public ?string $keterangan = null,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        $kodeMapel = trim($payload->getString('kode_mapel', (string) $request->request->get('kode_mapel', '')));
        $namaMapel = trim($payload->getString('nama_mapel', (string) $request->request->get('nama_mapel', '')));
        
        $kelompokStr = $payload->getString('kelompok', (string) $request->request->get('kelompok', 'wajib'));
        $kelompok = KelompokMapel::tryFrom($kelompokStr) ?? KelompokMapel::WAJIB;

        $tingkat = $payload->has('tingkat') && $payload->get('tingkat') !== null && $payload->get('tingkat') !== ''
            ? (int) $payload->get('tingkat')
            : null;

        $jurusan = $payload->has('jurusan') && $payload->getString('jurusan') !== ''
            ? trim($payload->getString('jurusan'))
            : null;

        $kkm = (int) $payload->get('kkm', $request->request->get('kkm', 75));
        $bebanJamPerMinggu = (int) $payload->get('beban_jam_per_minggu', $request->request->get('beban_jam_per_minggu', 2));

        $rawGuruIds = $payload->has('guru_ids') ? $payload->all('guru_ids') : $request->request->all('guru_ids');
        if (!is_array($rawGuruIds)) {
            $rawGuruIds = [];
        }
        $guruPengampuIds = array_values(array_unique(array_filter(array_map('intval', $rawGuruIds), fn($id) => $id > 0)));

        $guruKoordinatorId = $payload->has('guru_koordinator_id') && $payload->get('guru_koordinator_id')
            ? (int) $payload->get('guru_koordinator_id')
            : null;

        $urutan = (int) $payload->get('urutan', $request->request->get('urutan', 0));
        $keterangan = $payload->has('keterangan') && $payload->getString('keterangan') !== ''
            ? trim($payload->getString('keterangan'))
            : null;

        $isActive = $payload->getBoolean('is_active', (bool) $request->request->get('is_active', true));

        return new self(
            kodeMapel: strtoupper($kodeMapel),
            namaMapel: $namaMapel,
            kelompok: $kelompok,
            tingkat: $tingkat,
            jurusan: $jurusan,
            kkm: $kkm > 0 ? $kkm : 75,
            bebanJamPerMinggu: $bebanJamPerMinggu > 0 ? $bebanJamPerMinggu : 2,
            guruPengampuIds: $guruPengampuIds,
            guruKoordinatorId: $guruKoordinatorId,
            urutan: $urutan,
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

        if ($this->kodeMapel === '') {
            $errors['kode_mapel'] = 'Kode mata pelajaran wajib diisi.';
        }

        if ($this->namaMapel === '') {
            $errors['nama_mapel'] = 'Nama mata pelajaran wajib diisi.';
        }

        if ($this->kkm < 0 || $this->kkm > 100) {
            $errors['kkm'] = 'KKM harus bernilai antara 0 sampai 100.';
        }

        if ($this->bebanJamPerMinggu <= 0) {
            $errors['beban_jam_per_minggu'] = 'Beban jam mengajar (JP) per minggu harus lebih dari 0.';
        }

        return $errors;
    }
}
