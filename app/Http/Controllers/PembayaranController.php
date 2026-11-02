<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PembayaranController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $query = Pembayaran::with(['tagihan.kontrakSewa.penyewa.user', 'tagihan.kontrakSewa.kamar', 'verifikator']);

        if ($user && $user->role === 'penyewa') {
            $penyewaId = $user->penyewa?->id ?? 0;
            $query->whereHas('tagihan.kontrakSewa', function ($q) use ($penyewaId) {
                $q->where('penyewa_id', $penyewaId);
            });
        }

        $pembayaran = $query->get();

        if ($pembayaran->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $pembayaran,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tagihan_id'        => 'required|exists:tagihan,id',
            'tanggal_bayar'     => 'nullable|date',
            'jumlah_bayar'      => 'required|numeric|min:1',
            'metode_pembayaran' => 'required|in:tunai,transfer,qris',
            'bukti_bayar'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        do {
            $kodePembayaran = 'PAY-' . date('Ym') . '-' . rand(100, 999);
            $exists = Pembayaran::where('kode_pembayaran', $kodePembayaran)->exists();
        } while ($exists);

        $buktiPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiPath = $request->file('bukti_bayar')->store('bukti_bayar', 'public');
        }

        $pembayaran = Pembayaran::create([
            'kode_pembayaran'   => $kodePembayaran,
            'tagihan_id'        => $request->tagihan_id,
            'tanggal_bayar'     => $request->tanggal_bayar ?? now(),
            'jumlah_bayar'      => $request->jumlah_bayar,
            'metode_pembayaran' => $request->metode_pembayaran,
            'bukti_bayar'       => $buktiPath,
            'status_pembayaran' => 'menunggu_verifikasi',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource add successfully!',
            'data'    => $pembayaran->load('tagihan')
        ], 201);
    }

    public function show(string $id)
    {
        $pembayaran = Pembayaran::with(['tagihan.kontrakSewa.penyewa.user', 'tagihan.kontrakSewa.kamar', 'verifikator'])->find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $pembayaran,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $pembayaran = Pembayaran::find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_pembayaran'  => 'required|in:menunggu_verifikasi,diterima,ditolak',
            'catatan_verifikasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $pembayaran) {
            $pembayaran->update([
                'status_pembayaran'  => $request->status_pembayaran,
                'diverifikasi_oleh'  => auth('api')->id(),
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ]);

            // Hitung total pembayaran yang sudah diterima untuk tagihan ini
            $tagihan = Tagihan::find($pembayaran->tagihan_id);
            if ($tagihan) {
                $totalDiterima = Pembayaran::where('tagihan_id', $tagihan->id)
                    ->where('status_pembayaran', 'diterima')
                    ->sum('jumlah_bayar');

                if ($totalDiterima >= $tagihan->jumlah_tagihan) {
                    $tagihan->update(['status_tagihan' => 'lunas']);
                } elseif ($totalDiterima > 0) {
                    $tagihan->update(['status_tagihan' => 'sebagian']);
                } else {
                    $tagihan->update(['status_tagihan' => 'belum_bayar']);
                }

                // Jika pembayaran diterima dan kontrak masih berstatus 'menunggu' (booking baru), otomatis aktifkan kontrak & kamar
                if ($request->status_pembayaran === 'diterima' && $tagihan->kontrakSewa) {
                    $kontrak = $tagihan->kontrakSewa;
                    if ($kontrak->status_kontrak === 'menunggu') {
                        $kontrak->update(['status_kontrak' => 'aktif']);
                        if ($kontrak->kamar) {
                            $kontrak->kamar->update(['status_kamar' => 'terisi']);
                        }
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'resource update successfully!',
            'data'    => $pembayaran->load(['tagihan', 'verifikator'])
        ], 200);
    }

    public function destroy(string $id)
    {
        $pembayaran = Pembayaran::find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        if ($pembayaran->bukti_bayar) {
            $oldPath = public_path('storage/' . $pembayaran->bukti_bayar);
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $pembayaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'resource delete successfully!'
        ], 200);
    }
}
