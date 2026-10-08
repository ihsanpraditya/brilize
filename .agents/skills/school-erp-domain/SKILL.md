---
name: school-erp-domain
description: >-
  Use this skill when designing, implementing, or modifying School ERP business logic,
  data models, entity relationships, workflows, and role-based permissions (RBAC).
---

# School ERP (ERP Sekolah) Domain & Architecture Guide

Panduan domain model, relasi entitas, modul bisnis, dan hak akses untuk sistem informasi ERP Sekolah.

---

## 1. Modul Utama ERP Sekolah

```
+-------------------------------------------------------------------------------+
|                                ERP SEKOLAH                                    |
+-------------------+-------------------+-------------------+-------------------+
|     AKADEMIK      |  KESISWAAN & PPDB |   KEUANGAN (SPP)  |   KEPEGAWAIAN/TU  |
| - Kurikulum/Mapel | - Pendaftaran     | - Pos Tarif/Biaya | - Data PTK / Guru |
| - Kelas & Rombel  | - Buku Induk      | - Tagihan Bulanan | - Presensi Guru   |
| - Jadwal & Roster | - Presensi Harian | - Pembayaran SPP  | - Penggajian      |
| - Nilai & Rapor   | - BK & Disiplin   | - Kas Masuk/Keluar| - Surat & Berkas  |
+-------------------+-------------------+-------------------+-------------------+
```

---

## 2. Struktur Data & Relasi Entitas (Doctrine Entity Design)

### A. Modul Akademik & Siswa
- **`TahunAjaran`**: `id`, `tahun` (e.g. "2026/2027"), `semester` (`Ganjil` | `Genap`), `isActive` (boolean).
- **`Jurusan` / `Peminatan`**: `id`, `kode`, `nama` (e.g., IPA, IPS, RPL, TKJ).
- **`Kelas` / `Rombel`**: `id`, `namaKelas` (e.g. "X-RPL-1"), `tingkat` (10, 11, 12 / 7, 8, 9), `waliKelas` (ManyToOne `Guru`), `tahunAjaran` (ManyToOne `TahunAjaran`).
- **`Siswa`**:
  - `id`, `nis`, `nisn`, `nik`, `namaLengkap`, `jenisKelamin` (`L`/`P`), `tempatLahir`, `tanggalLahir`, `alamat`, `nomorHp`
  - `kelas` (ManyToOne `Kelas`)
  - `status` (`aktif`, `lulus`, `mutasi`, `keluar`)
  - `namaAyah`, `namaIbu`, `namaWali`, `kontakOrangTua`
- **`MataPelajaran`**: `id`, `kode`, `namaMapel`, `kelompok` (Wajib, Peminatan, Mulok), `kkm` / `kktp`.
- **`JadwalPelajaran`**: `id`, `kelas`, `mapel`, `guru`, `hari` (Senin-Sabtu), `jamMulai`, `jamSelesai`, `ruangan`.
- **`PresensiSiswa`**: `id`, `siswa`, `tanggal`, `status` (`hadir`, `sakit`, `izin`, `alpa`), `keterangan`.
- **`Penilaian` / `NilaiRapor`**: `id`, `siswa`, `mapel`, `jenisNilai` (Tugas, Formatif, Sumatif, UTS, UAS), `skor`, `catatanGuru`.

### B. Modul Keuangan & SPP
- **`PosKeuangan`**: `id`, `namaPos` (SPP Bulanan, Uang Gedung, Seragam, Ujian, Ekstrakurikuler).
- **`TarifPembayaran`**: `id`, `posKeuangan`, `tahunAjaran`, `tingkat` / `jurusan` / `siswa`, `nominal`.
- **`TagihanSiswa`**: `id`, `siswa`, `posKeuangan`, `bulan` (1..12), `tahun`, `nominalTagihan`, `sisaTagihan`, `status` (`lunas`, `belum_lunas`, `sebagian`).
- **`TransaksiPembayaran`**: `id`, `kodeTransaksi`, `tagihan`, `tanggalBayar`, `jumlahBayar`, `metode` (`tunai`, `transfer`, `qris`), `petugas` (ManyToOne `User`), `kuitansiNumber`.

### C. Modul Kepegawaian & Guru (PTK)
- **`Pegawai` / `Guru`**: `id`, `nip`, `nuptk`, `namaLengkap`, `gelarDepan`, `gelarBelakang`, `jenisPegawai` (`guru`, `staf_tu`, `satpam`, `kebersihan`), `jabatan`, `statusKepegawaian` (`PNS`, `PPPK`, `GTT`, `GTY`).

---

## 3. Role-Based Access Control (RBAC)

Hierarki peran dalam ERP Sekolah:

| Role | Kode Role Symfony | Lingkup Wewenang |
| :--- | :--- | :--- |
| **Super Admin** | `ROLE_SUPER_ADMIN` | Konfigurasi sistem, manajemen user, backup database, master data tahun ajaran. |
| **Kepala Sekolah** | `ROLE_KEPSEK` | Akses view-all laporan akademik, keuangan, statistik kehadiran, approval rapor. |
| **Tata Usaha (TU)** | `ROLE_TU` | Master data siswa, guru, rombel, mutasi, surat menyurat, cetak kartu pelajar. |
| **Bendahara** | `ROLE_BENDAHARA` | Master pos pembayaran, penagihan SPP, pencatatan transaksi kasir, cetak kuitansi. |
| **Guru / Wali Kelas**| `ROLE_GURU` | Input presensi harian/mapel, input nilai siswa, cetak leger/rapor, catatan BK. |
| **Siswa / Orang Tua**| `ROLE_SISWA`, `ROLE_WALI` | Portal mandiri: Cek nilai/rapor, riwayat presensi, cek status tagihan SPP. |

---

## 4. Konvensi Bisnis & Integritas Data

1. **Academic Year Scoping**: Semua data transaksi kelas, rombel, jadwal, dan nilai wajib berelasi dengan `TahunAjaran` aktif.
2. **Soft Deletes / Status Flagging**: Data master sensitif (Siswa, Guru, Nilai, Transaksi Keuangan) tidak boleh di-`HARD DELETE` sembarangan; gunakan kolom `status` atau timestamp audit (`deletedAt`).
3. **Audit Trail**: Setiap entitas mutasi penting wajib memiliki `createdAt`, `updatedAt`, dan `createdBy` (relasi ke `User`).
4. **Number Generation Pattern**:
   - Nomor Transaksi Pembayaran: `TRX/YYYYMMDD/XXXX` (e.g. `TRX/20261008/0001`)
   - Nomor Surat: `NOMOR/KODE_SURAT/BULAN_ROMAWI/TAHUN`
