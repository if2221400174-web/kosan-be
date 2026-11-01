<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Galeri;
use App\Models\Kamar;
use App\Models\KontrakSewa;
use App\Models\Laporan;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Pengumuman;
use App\Models\Penyewa;
use App\Models\ProfilKos;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Seeder;

class KosanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::whereIn('role', ['administrator', 'admin'])->first();
        $userPenyewa = User::where('role', 'penyewa')->first();

        // 1. Profil Kos
        ProfilKos::create([
            'nama_kos'        => 'Kos Griya Harmoni',
            'slug'            => 'kos-griya-harmoni',
            'tagline'         => 'Hunian Nyaman, Aman, dan Strategis',
            'deskripsi'       => 'Kos eksklusif dengan fasilitas lengkap, dekat kampus dan pusat perbelanjaan.',
            'alamat'          => 'Jl. Merdeka No. 45, Kota Bandung',
            'google_maps_url' => 'https://maps.google.com',
            'telepon'         => '022-7654321',
            'email'           => 'info@griyaharmoni.com',
            'whatsapp'        => '081234567890',
            'info_rekening'   => 'BCA 1234567890 a.n. Griya Harmoni',
            'aturan_kos'      => '1. Tamu menginap wajib lapor. 2. Jaga kebersihan dan ketenangan.',
            'jam_malam'       => '23:00:00',
        ]);

        // 2. Penyewa
        $penyewa = Penyewa::create([
            'user_id'              => $userPenyewa->id,
            'nik'                  => '3201012345670001',
            'jenis_kelamin'        => 'laki-laki',
            'alamat_asal'          => 'Jl. Sudirman No. 10, Jakarta',
            'pekerjaan'            => 'Mahasiswa',
            'nama_kontak_darurat'  => 'Hendro Santoso (Ayah)',
            'nomor_kontak_darurat' => '081122334455',
            'foto_ktp'             => null,
        ]);

        // 3. Fasilitas Kamar & Umum
        $ac = Fasilitas::create([
            'nama_fasilitas'  => 'AC',
            'jenis_fasilitas' => 'kamar',
            'ikon'            => 'snowflake',
            'deskripsi'       => 'Pendingin ruangan 1/2 PK',
        ]);

        $kmDalam = Fasilitas::create([
            'nama_fasilitas'  => 'Kamar Mandi Dalam',
            'jenis_fasilitas' => 'kamar',
            'ikon'            => 'bath',
            'deskripsi'       => 'Kloset duduk & shower',
        ]);

        $wifi = Fasilitas::create([
            'nama_fasilitas'  => 'WiFi Cepat',
            'jenis_fasilitas' => 'umum',
            'ikon'            => 'wifi',
            'deskripsi'       => 'Internet fiber optik 100 Mbps',
        ]);

        $dapur = Fasilitas::create([
            'nama_fasilitas'  => 'Dapur Bersama',
            'jenis_fasilitas' => 'umum',
            'ikon'            => 'utensils',
            'deskripsi'       => 'Kompor, kulkas, dan dispenser',
        ]);

        // 4. Kamar
        $kamar1 = Kamar::create([
            'nomor_kamar'   => 'A-01',
            'tipe_kamar'    => 'VIP',
            'lantai'        => 1,
            'harga_bulanan' => 1500000,
            'harga_tahunan' => 16500000,
            'luas_kamar'    => '3x4 m',
            'deskripsi'     => 'Kamar lantai 1 menghadap taman',
            'status_kamar'  => 'terisi',
        ]);

        $kamar2 = Kamar::create([
            'nomor_kamar'   => 'A-02',
            'tipe_kamar'    => 'Reguler',
            'lantai'        => 1,
            'harga_bulanan' => 1000000,
            'harga_tahunan' => 11000000,
            'luas_kamar'    => '3x3 m',
            'deskripsi'     => 'Kamar nyaman dan bersih',
            'status_kamar'  => 'tersedia',
        ]);

        // 5. Kamar Fasilitas (Pivot)
        $kamar1->fasilitas()->attach([$ac->id, $kmDalam->id]);
        $kamar2->fasilitas()->attach([$kmDalam->id]);

        // 6. Galeri
        Galeri::create([
            'kamar_id'         => null,
            'judul'            => 'Tampak Depan Kos Griya Harmoni',
            'foto'             => 'galeri/default-depan.jpg',
            'kategori'         => 'Tampak Depan',
            'urutan'           => 1,
            'status_publikasi' => 'terbit',
        ]);

        Galeri::create([
            'kamar_id'         => $kamar1->id,
            'judul'            => 'Interior Kamar VIP A-01',
            'foto'             => 'galeri/default-kamar-a01.jpg',
            'kategori'         => 'Detail Kamar',
            'urutan'           => 2,
            'status_publikasi' => 'terbit',
        ]);

        // 7. Kontrak Sewa
        $kontrak = KontrakSewa::create([
            'kode_kontrak'    => 'KTR-202611-001',
            'penyewa_id'      => $penyewa->id,
            'kamar_id'        => $kamar1->id,
            'tanggal_mulai'   => '2026-11-01',
            'tanggal_selesai' => '2026-12-01',
            'tipe_sewa'       => 'bulanan',
            'harga_sewa'      => 1500000,
            'status_kontrak'  => 'aktif',
            'catatan'         => 'Sewa perdana kamar A-01',
        ]);

        // 8. Tagihan
        $tagihan = Tagihan::create([
            'kode_tagihan'        => 'INV-202611-001',
            'kontrak_sewa_id'     => $kontrak->id,
            'periode_mulai'       => '2026-11-01',
            'periode_selesai'     => '2026-11-30',
            'tanggal_terbit'      => '2026-11-01',
            'tanggal_jatuh_tempo' => '2026-11-05',
            'nominal_pokok'       => 1500000,
            'denda'               => 0,
            'jumlah_tagihan'      => 1500000,
            'status_tagihan'      => 'lunas',
            'keterangan'          => 'Tagihan sewa bulan November 2026',
        ]);

        // 9. Pembayaran
        Pembayaran::create([
            'kode_pembayaran'    => 'PAY-202611-001',
            'tagihan_id'         => $tagihan->id,
            'tanggal_bayar'      => now(),
            'jumlah_bayar'       => 1500000,
            'metode_pembayaran'  => 'transfer',
            'bukti_bayar'        => null,
            'status_pembayaran'  => 'diterima',
            'diverifikasi_oleh'  => $admin->id,
            'tanggal_verifikasi' => now(),
            'catatan_verifikasi' => 'Pembayaran sesuai mutasi rekening',
        ]);

        // 10. Laporan
        Laporan::create([
            'kode_laporan'    => 'LAP-202611-001',
            'penyewa_id'      => $penyewa->id,
            'kamar_id'        => $kamar1->id,
            'judul'           => 'Remot AC Kurang Responsif',
            'deskripsi'       => 'Baterai remot AC sepertinya habis.',
            'status'          => 'diproses',
            'ditangani_oleh'  => $admin->id,
            'catatan_admin'   => 'Akan diganti baterai baru sore ini.',
        ]);

        // 11. Pengumuman
        Pengumuman::create([
            'admin_id'          => $admin->id,
            'judul'             => 'Pembersihan Tandon Air Rutin',
            'isi'               => 'Hari Minggu pukul 09.00 - 11.00 WIB akan dilakukan pengurasan tandon air.',
            'tanggal_publikasi' => '2026-11-01',
            'tanggal_berakhir'  => '2026-11-08',
            'status'            => 'terbit',
        ]);

        // 12. Pengeluaran
        Pengeluaran::create([
            'admin_id'            => $admin->id,
            'kode_pengeluaran'    => 'OUT-202611-001',
            'kategori'            => 'internet',
            'nama_pengeluaran'    => 'Tagihan WiFi Indihome November',
            'tanggal_pengeluaran' => '2026-11-01',
            'jumlah'              => 450000,
            'keterangan'          => 'Paket 100 Mbps',
        ]);
    }
}
