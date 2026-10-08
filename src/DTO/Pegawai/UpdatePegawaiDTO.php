<?php

declare(strict_types=1);

namespace App\DTO\Pegawai;

use App\Enum\JenisKelamin;
use App\Enum\KategoriPegawai;
use App\Enum\StatusKepegawaian;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdatePegawaiDTO
{
    public function __construct(
        public string $namaLengkap,
        public ?string $gelarDepan = null,
        public ?string $gelarBelakang = null,
        public ?string $nip = null,
        public ?string $nuptk = null,
        public ?string $nik = null,
        public JenisKelamin $jenisKelamin = JenisKelamin::LAKI_LAKI,
        public ?string $tempatLahir = null,
        public ?string $tanggalLahir = null,
        public ?string $agama = null,
        public ?string $alamat = null,
        public ?string $nomorHp = null,
        public ?string $email = null,
        public KategoriPegawai $kategoriPegawai = KategoriPegawai::PENDIDIK,
        public string $jenisPegawai = 'guru',
        public StatusKepegawaian $statusKepegawaian = StatusKepegawaian::HONORER,
        public ?string $jabatan = null,
        public ?string $pendidikanTerakhir = 'S1',
        public ?string $jurusanPendidikan = null,
        public ?string $tanggalMasuk = null,
        public bool $isActive = true,
        // Bidang khusus Guru
        public ?string $bidangStudiUtama = null,
        public bool $isSertifikasi = false,
        public ?string $noSertifikasi = null,
        public ?string $tugasTambahan = null,
        public ?string $tmtPendidik = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        $namaLengkap = trim($payload->getString('nama_lengkap', (string) $request->request->get('nama_lengkap', '')));
        $gelarDepan = $payload->has('gelar_depan') && $payload->getString('gelar_depan') !== '' ? trim($payload->getString('gelar_depan')) : null;
        $gelarBelakang = $payload->has('gelar_belakang') && $payload->getString('gelar_belakang') !== '' ? trim($payload->getString('gelar_belakang')) : null;

        $nip = $payload->has('nip') && $payload->getString('nip') !== '' ? trim($payload->getString('nip')) : null;
        $nuptk = $payload->has('nuptk') && $payload->getString('nuptk') !== '' ? trim($payload->getString('nuptk')) : null;
        $nik = $payload->has('nik') && $payload->getString('nik') !== '' ? trim($payload->getString('nik')) : null;

        $jkStr = $payload->getString('jenis_kelamin', (string) $request->request->get('jenis_kelamin', JenisKelamin::LAKI_LAKI->value));
        $jenisKelamin = JenisKelamin::tryFrom($jkStr) ?? JenisKelamin::LAKI_LAKI;

        $tempatLahir = $payload->has('tempat_lahir') && $payload->getString('tempat_lahir') !== '' ? trim($payload->getString('tempat_lahir')) : null;
        $tanggalLahir = $payload->has('tanggal_lahir') && $payload->getString('tanggal_lahir') !== '' ? $payload->getString('tanggal_lahir') : null;
        $agama = $payload->has('agama') && $payload->getString('agama') !== '' ? trim($payload->getString('agama')) : null;
        $alamat = $payload->has('alamat') && $payload->getString('alamat') !== '' ? trim($payload->getString('alamat')) : null;
        $nomorHp = $payload->has('nomor_hp') && $payload->getString('nomor_hp') !== '' ? trim($payload->getString('nomor_hp')) : null;
        $email = $payload->has('email') && $payload->getString('email') !== '' ? trim($payload->getString('email')) : null;

        $katStr = $payload->getString('kategori_pegawai', (string) $request->request->get('kategori_pegawai', KategoriPegawai::PENDIDIK->value));
        $kategoriPegawai = KategoriPegawai::tryFrom($katStr) ?? KategoriPegawai::PENDIDIK;

        $jenisPegawai = $payload->getString('jenis_pegawai', (string) $request->request->get('jenis_pegawai', 'guru'));

        $statusStr = $payload->getString('status_kepegawaian', (string) $request->request->get('status_kepegawaian', StatusKepegawaian::HONORER->value));
        $statusKepegawaian = StatusKepegawaian::tryFrom($statusStr) ?? StatusKepegawaian::HONORER;

        $jabatan = $payload->has('jabatan') && $payload->getString('jabatan') !== '' ? trim($payload->getString('jabatan')) : null;
        $pendidikanTerakhir = $payload->has('pendidikan_terakhir') && $payload->getString('pendidikan_terakhir') !== '' ? trim($payload->getString('pendidikan_terakhir')) : 'S1';
        $jurusanPendidikan = $payload->has('jurusan_pendidikan') && $payload->getString('jurusan_pendidikan') !== '' ? trim($payload->getString('jurusan_pendidikan')) : null;
        $tanggalMasuk = $payload->has('tanggal_masuk') && $payload->getString('tanggal_masuk') !== '' ? $payload->getString('tanggal_masuk') : null;
        $isActive = $payload->getBoolean('is_active', (bool) $request->request->get('is_active', true));

        // Field Guru
        $bidangStudiUtama = $payload->has('bidang_studi_utama') && $payload->getString('bidang_studi_utama') !== '' ? trim($payload->getString('bidang_studi_utama')) : null;
        $isSertifikasi = $payload->getBoolean('is_sertifikasi', (bool) $request->request->get('is_sertifikasi', false));
        $noSertifikasi = $payload->has('no_sertifikasi') && $payload->getString('no_sertifikasi') !== '' ? trim($payload->getString('no_sertifikasi')) : null;
        $tugasTambahan = $payload->has('tugas_tambahan') && $payload->getString('tugas_tambahan') !== '' ? trim($payload->getString('tugas_tambahan')) : null;
        $tmtPendidik = $payload->has('tmt_pendidik') && $payload->getString('tmt_pendidik') !== '' ? $payload->getString('tmt_pendidik') : null;

        return new self(
            namaLengkap: $namaLengkap,
            gelarDepan: $gelarDepan,
            gelarBelakang: $gelarBelakang,
            nip: $nip,
            nuptk: $nuptk,
            nik: $nik,
            jenisKelamin: $jenisKelamin,
            tempatLahir: $tempatLahir,
            tanggalLahir: $tanggalLahir,
            agama: $agama,
            alamat: $alamat,
            nomorHp: $nomorHp,
            email: $email,
            kategoriPegawai: $kategoriPegawai,
            jenisPegawai: $jenisPegawai,
            statusKepegawaian: $statusKepegawaian,
            jabatan: $jabatan,
            pendidikanTerakhir: $pendidikanTerakhir,
            jurusanPendidikan: $jurusanPendidikan,
            tanggalMasuk: $tanggalMasuk,
            isActive: $isActive,
            bidangStudiUtama: $bidangStudiUtama,
            isSertifikasi: $isSertifikasi,
            noSertifikasi: $noSertifikasi,
            tugasTambahan: $tugasTambahan,
            tmtPendidik: $tmtPendidik,
        );
    }

    /**
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->namaLengkap === '') {
            $errors['nama_lengkap'] = 'Nama lengkap pegawai / guru wajib diisi.';
        }

        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        return $errors;
    }
}
