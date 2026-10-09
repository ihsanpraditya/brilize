<?php

declare(strict_types=1);

namespace App\DTO\Siswa;

use App\Enum\JenisKelamin;
use App\Enum\StatusSiswa;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdateSiswaDTO
{
    public function __construct(
        public string $nis,
        public ?string $nisn = null,
        public ?string $nik = null,
        public string $namaLengkap = '',
        public ?string $namaPanggilan = null,
        public ?string $namaArab = null,
        public JenisKelamin $jenisKelamin = JenisKelamin::LAKI_LAKI,
        public ?string $tempatLahir = null,
        public ?string $tanggalLahir = null,
        public ?string $agama = null,
        public ?string $alamat = null,
        public ?string $rt = null,
        public ?string $rw = null,
        public ?string $kelurahan = null,
        public ?string $kecamatan = null,
        public ?string $kabupatenKota = null,
        public ?string $provinsi = null,
        public ?string $kodePos = null,
        public ?string $nomorHp = null,
        public ?string $email = null,
        public ?int $kelasId = null,
        public StatusSiswa $status = StatusSiswa::AKTIF,
        public ?string $namaAyah = null,
        public ?string $pekerjaanAyah = null,
        public ?string $penghasilanAyah = null,
        public ?string $noHpAyah = null,
        public ?string $namaIbu = null,
        public ?string $pekerjaanIbu = null,
        public ?string $penghasilanIbu = null,
        public ?string $noHpIbu = null,
        public ?string $namaWali = null,
        public ?string $hubunganWali = null,
        public ?string $pekerjaanWali = null,
        public ?string $noHpWali = null,
        public ?string $alamatOrangTua = null,
        public ?int $anakKe = null,
        public ?int $jumlahSaudara = null,
        public ?string $tanggalMasuk = null,
        public ?string $asalSekolah = null,
        public ?string $fotoUrl = null,
        public ?string $catatan = null,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        $nis = trim($payload->getString('nis', (string) $request->request->get('nis', '')));
        $nisn = $payload->has('nisn') && $payload->getString('nisn') !== '' ? trim($payload->getString('nisn')) : null;
        $nik = $payload->has('nik') && $payload->getString('nik') !== '' ? trim($payload->getString('nik')) : null;

        $namaLengkap = trim($payload->getString('nama_lengkap', (string) $request->request->get('nama_lengkap', '')));
        $namaPanggilan = $payload->has('nama_panggilan') && $payload->getString('nama_panggilan') !== '' ? trim($payload->getString('nama_panggilan')) : null;
        $namaArab = $payload->has('nama_arab') && $payload->getString('nama_arab') !== '' ? trim($payload->getString('nama_arab')) : null;

        $jkStr = $payload->getString('jenis_kelamin', (string) $request->request->get('jenis_kelamin', JenisKelamin::LAKI_LAKI->value));
        $jenisKelamin = JenisKelamin::tryFrom($jkStr) ?? JenisKelamin::LAKI_LAKI;

        $tempatLahir = $payload->has('tempat_lahir') && $payload->getString('tempat_lahir') !== '' ? trim($payload->getString('tempat_lahir')) : null;
        $tanggalLahir = $payload->has('tanggal_lahir') && $payload->getString('tanggal_lahir') !== '' ? trim($payload->getString('tanggal_lahir')) : null;
        $agama = $payload->has('agama') && $payload->getString('agama') !== '' ? trim($payload->getString('agama')) : null;
        $alamat = $payload->has('alamat') && $payload->getString('alamat') !== '' ? trim($payload->getString('alamat')) : null;

        $rt = $payload->has('rt') && $payload->getString('rt') !== '' ? trim($payload->getString('rt')) : null;
        $rw = $payload->has('rw') && $payload->getString('rw') !== '' ? trim($payload->getString('rw')) : null;
        $kelurahan = $payload->has('kelurahan') && $payload->getString('kelurahan') !== '' ? trim($payload->getString('kelurahan')) : null;
        $kecamatan = $payload->has('kecamatan') && $payload->getString('kecamatan') !== '' ? trim($payload->getString('kecamatan')) : null;
        $kabupatenKota = $payload->has('kabupaten_kota') && $payload->getString('kabupaten_kota') !== '' ? trim($payload->getString('kabupaten_kota')) : null;
        $provinsi = $payload->has('provinsi') && $payload->getString('provinsi') !== '' ? trim($payload->getString('provinsi')) : null;
        $kodePos = $payload->has('kode_pos') && $payload->getString('kode_pos') !== '' ? trim($payload->getString('kode_pos')) : null;

        $nomorHp = $payload->has('nomor_hp') && $payload->getString('nomor_hp') !== '' ? trim($payload->getString('nomor_hp')) : null;
        $email = $payload->has('email') && $payload->getString('email') !== '' ? trim(strtolower($payload->getString('email'))) : null;

        $kelasId = $payload->has('kelas_id') && $payload->get('kelas_id') !== '' && $payload->get('kelas_id') !== null
            ? (int) $payload->get('kelas_id')
            : null;

        $statusStr = $payload->getString('status', (string) $request->request->get('status', StatusSiswa::AKTIF->value));
        $status = StatusSiswa::tryFrom($statusStr) ?? StatusSiswa::AKTIF;

        $namaAyah = $payload->has('nama_ayah') && $payload->getString('nama_ayah') !== '' ? trim($payload->getString('nama_ayah')) : null;
        $pekerjaanAyah = $payload->has('pekerjaan_ayah') && $payload->getString('pekerjaan_ayah') !== '' ? trim($payload->getString('pekerjaan_ayah')) : null;
        $penghasilanAyah = $payload->has('penghasilan_ayah') && $payload->getString('penghasilan_ayah') !== '' ? trim($payload->getString('penghasilan_ayah')) : null;
        $noHpAyah = $payload->has('no_hp_ayah') && $payload->getString('no_hp_ayah') !== '' ? trim($payload->getString('no_hp_ayah')) : null;

        $namaIbu = $payload->has('nama_ibu') && $payload->getString('nama_ibu') !== '' ? trim($payload->getString('nama_ibu')) : null;
        $pekerjaanIbu = $payload->has('pekerjaan_ibu') && $payload->getString('pekerjaan_ibu') !== '' ? trim($payload->getString('pekerjaan_ibu')) : null;
        $penghasilanIbu = $payload->has('penghasilan_ibu') && $payload->getString('penghasilan_ibu') !== '' ? trim($payload->getString('penghasilan_ibu')) : null;
        $noHpIbu = $payload->has('no_hp_ibu') && $payload->getString('no_hp_ibu') !== '' ? trim($payload->getString('no_hp_ibu')) : null;

        $namaWali = $payload->has('nama_wali') && $payload->getString('nama_wali') !== '' ? trim($payload->getString('nama_wali')) : null;
        $hubunganWali = $payload->has('hubungan_wali') && $payload->getString('hubungan_wali') !== '' ? trim($payload->getString('hubungan_wali')) : null;
        $pekerjaanWali = $payload->has('pekerjaan_wali') && $payload->getString('pekerjaan_wali') !== '' ? trim($payload->getString('pekerjaan_wali')) : null;
        $noHpWali = $payload->has('no_hp_wali') && $payload->getString('no_hp_wali') !== '' ? trim($payload->getString('no_hp_wali')) : null;
        $alamatOrangTua = $payload->has('alamat_orang_tua') && $payload->getString('alamat_orang_tua') !== '' ? trim($payload->getString('alamat_orang_tua')) : null;

        $anakKe = $payload->has('anak_ke') && $payload->get('anak_ke') !== '' && $payload->get('anak_ke') !== null
            ? (int) $payload->get('anak_ke')
            : null;
        $jumlahSaudara = $payload->has('jumlah_saudara') && $payload->get('jumlah_saudara') !== '' && $payload->get('jumlah_saudara') !== null
            ? (int) $payload->get('jumlah_saudara')
            : null;

        $tanggalMasuk = $payload->has('tanggal_masuk') && $payload->getString('tanggal_masuk') !== '' ? trim($payload->getString('tanggal_masuk')) : null;
        $asalSekolah = $payload->has('asal_sekolah') && $payload->getString('asal_sekolah') !== '' ? trim($payload->getString('asal_sekolah')) : null;
        $fotoUrl = $payload->has('foto_url') && $payload->getString('foto_url') !== '' ? trim($payload->getString('foto_url')) : null;
        $catatan = $payload->has('catatan') && $payload->getString('catatan') !== '' ? trim($payload->getString('catatan')) : null;

        $isActive = (bool) $payload->get('is_active', $request->request->get('is_active', true));

        return new self(
            nis: $nis,
            nisn: $nisn,
            nik: $nik,
            namaLengkap: $namaLengkap,
            namaPanggilan: $namaPanggilan,
            namaArab: $namaArab,
            jenisKelamin: $jenisKelamin,
            tempatLahir: $tempatLahir,
            tanggalLahir: $tanggalLahir,
            agama: $agama,
            alamat: $alamat,
            rt: $rt,
            rw: $rw,
            kelurahan: $kelurahan,
            kecamatan: $kecamatan,
            kabupatenKota: $kabupatenKota,
            provinsi: $provinsi,
            kodePos: $kodePos,
            nomorHp: $nomorHp,
            email: $email,
            kelasId: $kelasId,
            status: $status,
            namaAyah: $namaAyah,
            pekerjaanAyah: $pekerjaanAyah,
            penghasilanAyah: $penghasilanAyah,
            noHpAyah: $noHpAyah,
            namaIbu: $namaIbu,
            pekerjaanIbu: $pekerjaanIbu,
            penghasilanIbu: $penghasilanIbu,
            noHpIbu: $noHpIbu,
            namaWali: $namaWali,
            hubunganWali: $hubunganWali,
            pekerjaanWali: $pekerjaanWali,
            noHpWali: $noHpWali,
            alamatOrangTua: $alamatOrangTua,
            anakKe: $anakKe,
            jumlahSaudara: $jumlahSaudara,
            tanggalMasuk: $tanggalMasuk,
            asalSekolah: $asalSekolah,
            fotoUrl: $fotoUrl,
            catatan: $catatan,
            isActive: $isActive,
        );
    }

    /**
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->nis)) {
            $errors['nis'] = 'NIS wajib diisi.';
        } elseif (strlen($this->nis) > 30) {
            $errors['nis'] = 'NIS maksimal 30 karakter.';
        }

        if (empty($this->namaLengkap)) {
            $errors['nama_lengkap'] = 'Nama lengkap siswa wajib diisi.';
        } elseif (strlen($this->namaLengkap) > 150) {
            $errors['nama_lengkap'] = 'Nama lengkap maksimal 150 karakter.';
        }

        if ($this->nisn !== null && strlen($this->nisn) > 20) {
            $errors['nisn'] = 'NISN maksimal 20 karakter.';
        }

        if ($this->nik !== null && strlen($this->nik) > 20) {
            $errors['nik'] = 'NIK maksimal 20 karakter.';
        }

        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        return $errors;
    }
}
