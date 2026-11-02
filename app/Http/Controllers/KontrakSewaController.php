<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\KontrakSewa;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KontrakSewaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $query = KontrakSewa::with(['penyewa.user', 'kamar', 'tagihan']);

        if ($user && $user->role === 'penyewa') {
            $query->where('penyewa_id', $user->penyewa?->id ?? 0);
        }

        $kontrak = $query->get();

        if ($kontrak->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $kontrak,
        ], 200);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = auth('api')->user();

        if ($authUser && $authUser->role === 'penyewa') {
            if (!$authUser->penyewa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan lengkapi data identitas penghuni (KTP & alamat) terlebih dahulu sebelum melakukan pemesanan kamar.'
                ], 422);
            }
            $request->merge([
                'penyewa_id'     => $authUser->penyewa->id,
                'status_kontrak' => 'menunggu',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'penyewa_id'        => 'required|exists:penyewa,id',
            'kamar_id'          => 'required|exists:kamar,id',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after:tanggal_mulai',
            'tipe_sewa'         => 'required|in:bulanan,tahunan',
            'harga_sewa'        => 'nullable|numeric|min:0',
            'status_kontrak'    => 'nullable|in:menunggu,aktif,selesai,dibatalkan',
            'catatan'           => 'nullable|string',
            'metode_pembayaran' => 'nullable|in:tunai,transfer,qris',
            'bukti_bayar'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $kamar = Kamar::find($request->kamar_id);

        // Generate kode kontrak unik
        do {
            $kodeKontrak = 'KTR-' . date('Ym') . '-' . rand(100, 999);
            $exists = KontrakSewa::where('kode_kontrak', $kodeKontrak)->exists();
        } while ($exists);

        // Ambil harga kesepakatan (dari request atau default harga kamar)
        $hargaSewa = $request->harga_sewa ?? (
            $request->tipe_sewa === 'tahunan' && $kamar->harga_tahunan
                ? $kamar->harga_tahunan
                : $kamar->harga_bulanan
        );

        $statusKontrak = $request->status_kontrak ?? 'aktif';

        $buktiPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiPath = $request->file('bukti_bayar')->store('bukti_bayar', 'public');
        }

        $kontrak = DB::transaction(function () use ($request, $kodeKontrak, $hargaSewa, $statusKontrak, $kamar, $buktiPath) {
            $kontrak = KontrakSewa::create([
                'kode_kontrak'    => $kodeKontrak,
                'penyewa_id'      => $request->penyewa_id,
                'kamar_id'        => $request->kamar_id,
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'tipe_sewa'       => $request->tipe_sewa,
                'harga_sewa'      => $hargaSewa,
                'status_kontrak'  => $statusKontrak,
                'catatan'         => $request->catatan,
            ]);

            if ($statusKontrak === 'aktif') {
                $kamar->update(['status_kamar' => 'terisi']);
            }

            // Buat tagihan pertama otomatis untuk booking / kontrak baru
            do {
                $kodeTagihan = 'INV-' . date('Ym') . '-' . rand(100, 999);
                $invExists = Tagihan::where('kode_tagihan', $kodeTagihan)->exists();
            } while ($invExists);

            $tagihan = Tagihan::create([
                'kode_tagihan'        => $kodeTagihan,
                'kontrak_sewa_id'     => $kontrak->id,
                'periode_mulai'       => $request->tanggal_mulai,
                'periode_selesai'     => $request->tanggal_selesai,
                'tanggal_terbit'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => $request->tanggal_mulai,
                'nominal_pokok'       => $hargaSewa,
                'denda'               => 0,
                'jumlah_tagihan'      => $hargaSewa,
                'status_tagihan'      => 'belum_bayar',
                'keterangan'          => 'Tagihan sewa Kamar ' . $kamar->nomor_kamar,
            ]);

            // Jika calon penyewa langsung memilih metode pembayaran saat booking (cash / transfer / qris)
            if ($request->filled('metode_pembayaran')) {
                do {
                    $kodePay = 'PAY-' . date('Ym') . '-' . rand(100, 999);
                    $payExists = Pembayaran::where('kode_pembayaran', $kodePay)->exists();
                } while ($payExists);

                Pembayaran::create([
                    'kode_pembayaran'   => $kodePay,
                    'tagihan_id'        => $tagihan->id,
                    'tanggal_bayar'     => now(),
                    'jumlah_bayar'      => $hargaSewa,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'bukti_bayar'       => $buktiPath,
                    'status_pembayaran' => 'menunggu_verifikasi',
                ]);
            }

            return $kontrak;
        });

        return response()->json([
            'success' => true,
            'message' => 'resource add successfully!',
            'data'    => $kontrak->load(['penyewa.user', 'kamar', 'tagihan.pembayaran'])
        ], 201);
    }

    public function show(string $id)
    {
        $kontrak = KontrakSewa::with(['penyewa.user', 'kamar', 'tagihan.pembayaran'])->find($id);

        if (!$kontrak) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $kontrak,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $kontrak = KontrakSewa::find($id);

        if (!$kontrak) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'penyewa_id'      => 'required|exists:penyewa,id',
            'kamar_id'        => 'required|exists:kamar,id',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'tipe_sewa'       => 'required|in:bulanan,tahunan',
            'harga_sewa'      => 'required|numeric|min:0',
            'status_kontrak'  => 'required|in:menunggu,aktif,selesai,dibatalkan',
            'catatan'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $kontrak) {
            $oldKamarId = $kontrak->kamar_id;

            $kontrak->update([
                'penyewa_id'      => $request->penyewa_id,
                'kamar_id'        => $request->kamar_id,
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'tipe_sewa'       => $request->tipe_sewa,
                'harga_sewa'      => $request->harga_sewa,
                'status_kontrak'  => $request->status_kontrak,
                'catatan'         => $request->catatan,
            ]);

            // Sinkronisasi status kamar
            if ($oldKamarId != $request->kamar_id) {
                Kamar::where('id', $oldKamarId)->update(['status_kamar' => 'tersedia']);
            }

            if ($request->status_kontrak === 'aktif') {
                Kamar::where('id', $request->kamar_id)->update(['status_kamar' => 'terisi']);
            } elseif (in_array($request->status_kontrak, ['selesai', 'dibatalkan'])) {
                $stillActive = KontrakSewa::where('kamar_id', $request->kamar_id)
                    ->where('status_kontrak', 'aktif')
                    ->where('id', '!=', $kontrak->id)
                    ->exists();
                if (!$stillActive) {
                    Kamar::where('id', $request->kamar_id)->update(['status_kamar' => 'tersedia']);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'resource update successfully!',
            'data'    => $kontrak->load(['penyewa.user', 'kamar'])
        ], 200);
    }

    public function destroy(string $id)
    {
        $kontrak = KontrakSewa::find($id);

        if (!$kontrak) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $kontrak->delete();

        return response()->json([
            'success' => true,
            'message' => 'resource delete successfully!'
        ], 200);
    }
}
