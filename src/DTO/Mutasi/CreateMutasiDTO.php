<?php

declare(strict_types=1);

namespace App\DTO\Mutasi;

use App\Enum\JenisMutasi;
use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;

final readonly class CreateMutasiDTO
{
    public function __construct(
        public int $siswaId,
        public int $tahunPelajaranId,
        public JenisMutasi $jenis,
        public ?int $kelasAsalId = null,
        public ?int $kelasTujuanId = null,
        public string $tanggalMutasi = '',
        public ?string $nomorSurat = null,
        public ?string $sekolahAsalTujuan = null,
        public ?string $alasan = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        $siswaId = $payload->has('siswa_id') ? (int) $payload->get('siswa_id') : (int) $request->request->get('siswa_id');
        if ($siswaId <= 0) {
            throw new InvalidArgumentException('Siswa wajib dipilih.');
        }

        $tahunPelajaranId = $payload->has('tahun_pelajaran_id') ? (int) $payload->get('tahun_pelajaran_id') : (int) $request->request->get('tahun_pelajaran_id');
        if ($tahunPelajaranId <= 0) {
            throw new InvalidArgumentException('Tahun pelajaran wajib dipilih.');
        }

        $jenisStr = $payload->getString('jenis', (string) $request->request->get('jenis', ''));
        $jenis = JenisMutasi::tryFrom($jenisStr);
        if ($jenis === null) {
            throw new InvalidArgumentException('Jenis mutasi tidak valid.');
        }

        $kelasAsalId = $payload->has('kelas_asal_id') && $payload->get('kelas_asal_id')
            ? (int) $payload->get('kelas_asal_id')
            : ($request->request->has('kelas_asal_id') && $request->request->get('kelas_asal_id') ? (int) $request->request->get('kelas_asal_id') : null);

        $kelasTujuanId = $payload->has('kelas_tujuan_id') && $payload->get('kelas_tujuan_id')
            ? (int) $payload->get('kelas_tujuan_id')
            : ($request->request->has('kelas_tujuan_id') && $request->request->get('kelas_tujuan_id') ? (int) $request->request->get('kelas_tujuan_id') : null);

        $tanggalMutasi = $payload->getString(
            'tanggal_mutasi',
            (string) $request->request->get('tanggal_mutasi', (new \DateTimeImmutable())->format('Y-m-d'))
        );

        $nomorSurat = $payload->getString('nomor_surat', (string) $request->request->get('nomor_surat', ''));
        $sekolahAsalTujuan = $payload->getString('sekolah_asal_tujuan', (string) $request->request->get('sekolah_asal_tujuan', ''));
        $alasan = $payload->getString('alasan', (string) $request->request->get('alasan', ''));

        return new self(
            siswaId: $siswaId,
            tahunPelajaranId: $tahunPelajaranId,
            jenis: $jenis,
            kelasAsalId: $kelasAsalId,
            kelasTujuanId: $kelasTujuanId,
            tanggalMutasi: trim($tanggalMutasi) !== '' ? trim($tanggalMutasi) : (new \DateTimeImmutable())->format('Y-m-d'),
            nomorSurat: trim($nomorSurat) !== '' ? trim($nomorSurat) : null,
            sekolahAsalTujuan: trim($sekolahAsalTujuan) !== '' ? trim($sekolahAsalTujuan) : null,
            alasan: trim($alasan) !== '' ? trim($alasan) : null,
        );
    }
}
