<?php

declare(strict_types=1);

namespace App\DTO\Siswa;

use App\Enum\JenisKelamin;
use App\Enum\StatusSiswa;

final readonly class ImportSiswaItemDTO
{
    public function __construct(
        public string $nis,
        public string $namaLengkap,
        public JenisKelamin $jenisKelamin = JenisKelamin::LAKI_LAKI,
        public ?string $nisn = null,
        public ?string $nik = null,
        public ?string $namaPanggilan = null,
        public ?string $namaArab = null,
        public ?string $tempatLahir = null,
        public ?string $tanggalLahir = null, // Format YYYY-MM-DD
        public ?string $agama = 'Islam',
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
        public ?string $kelas = null, // Nama kelas atau ID kelas
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
        public ?string $catatan = null,
        public bool $isActive = true,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $nis = trim((string) ($data['nis'] ?? ''));
        $namaLengkap = trim((string) ($data['nama_lengkap'] ?? $data['namaLengkap'] ?? ''));

        // Normalisasi Jenis Kelamin
        $rawJk = strtoupper(trim((string) ($data['jenis_kelamin'] ?? $data['jenisKelamin'] ?? 'L')));
        $jk = JenisKelamin::LAKI_LAKI;
        if (str_starts_with($rawJk, 'P') || str_starts_with($rawJk, 'W') || $rawJk === 'FEMALE') {
            $jk = JenisKelamin::PEREMPUAN;
        }

        // Normalisasi Status Siswa
        $rawStatus = strtolower(trim((string) ($data['status'] ?? 'aktif')));
        $status = StatusSiswa::tryFrom($rawStatus) ?? StatusSiswa::AKTIF;

        $cleanString = static fn(mixed $val): ?string => !empty($val) ? trim((string) $val) : null;
        $cleanInt = static fn(mixed $val): ?int => !empty($val) ? (int) $val : null;

        return new self(
            nis: $nis,
            namaLengkap: $namaLengkap,
            jenisKelamin: $jk,
            nisn: $cleanString($data['nisn'] ?? null),
            nik: $cleanString($data['nik'] ?? null),
            namaPanggilan: $cleanString($data['nama_panggilan'] ?? $data['namaPanggilan'] ?? null),
            namaArab: $cleanString($data['nama_arab'] ?? $data['namaArab'] ?? null),
            tempatLahir: $cleanString($data['tempat_lahir'] ?? $data['tempatLahir'] ?? null),
            tanggalLahir: $cleanString($data['tanggal_lahir'] ?? $data['tanggalLahir'] ?? null),
            agama: $cleanString($data['agama'] ?? 'Islam') ?? 'Islam',
            alamat: $cleanString($data['alamat'] ?? null),
            rt: $cleanString($data['rt'] ?? null),
            rw: $cleanString($data['rw'] ?? null),
            kelurahan: $cleanString($data['kelurahan'] ?? null),
            kecamatan: $cleanString($data['kecamatan'] ?? null),
            kabupatenKota: $cleanString($data['kabupaten_kota'] ?? $data['kabupatenKota'] ?? null),
            provinsi: $cleanString($data['provinsi'] ?? null),
            kodePos: $cleanString($data['kode_pos'] ?? $data['kodePos'] ?? null),
            nomorHp: $cleanString($data['nomor_hp'] ?? $data['nomorHp'] ?? null),
            email: $cleanString($data['email'] ?? null),
            kelas: $cleanString($data['kelas'] ?? $data['kelas_id'] ?? null),
            status: $status,
            namaAyah: $cleanString($data['nama_ayah'] ?? $data['namaAyah'] ?? null),
            pekerjaanAyah: $cleanString($data['pekerjaan_ayah'] ?? $data['pekerjaanAyah'] ?? null),
            penghasilanAyah: $cleanString($data['penghasilan_ayah'] ?? $data['penghasilanAyah'] ?? null),
            noHpAyah: $cleanString($data['no_hp_ayah'] ?? $data['noHpAyah'] ?? null),
            namaIbu: $cleanString($data['nama_ibu'] ?? $data['namaIbu'] ?? null),
            pekerjaanIbu: $cleanString($data['pekerjaan_ibu'] ?? $data['pekerjaanIbu'] ?? null),
            penghasilanIbu: $cleanString($data['penghasilan_ibu'] ?? $data['penghasilanIbu'] ?? null),
            noHpIbu: $cleanString($data['no_hp_ibu'] ?? $data['noHpIbu'] ?? null),
            namaWali: $cleanString($data['nama_wali'] ?? $data['namaWali'] ?? null),
            hubunganWali: $cleanString($data['hubungan_wali'] ?? $data['hubunganWali'] ?? null),
            pekerjaanWali: $cleanString($data['pekerjaan_wali'] ?? $data['pekerjaanWali'] ?? null),
            noHpWali: $cleanString($data['no_hp_wali'] ?? $data['noHpWali'] ?? null),
            alamatOrangTua: $cleanString($data['alamat_orang_tua'] ?? $data['alamatOrangTua'] ?? null),
            anakKe: $cleanInt($data['anak_ke'] ?? $data['anakKe'] ?? null),
            jumlahSaudara: $cleanInt($data['jumlah_saudara'] ?? $data['jumlahSaudara'] ?? null),
            tanggalMasuk: $cleanString($data['tanggal_masuk'] ?? $data['tanggalMasuk'] ?? null),
            asalSekolah: $cleanString($data['asal_sekolah'] ?? $data['asalSekolah'] ?? null),
            catatan: $cleanString($data['catatan'] ?? null),
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : true,
        );
    }
}
